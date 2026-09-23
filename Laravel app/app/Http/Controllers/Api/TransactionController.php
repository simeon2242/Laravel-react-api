<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Transaction\IndexTransactionRequest;
use App\Http\Requests\Transaction\StoreTransactionRequest;
use App\Http\Requests\Transaction\UpdateTransactionRequest;
use App\Http\Resources\TransactionResource;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Response;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class TransactionController extends Controller
{
    public function index(
        IndexTransactionRequest $request
    ): AnonymousResourceCollection {
        $query = $request->user()
            ->transactions()
            ->with('category:id,name,color,icon');

        $validated = $request->validated();

        $query
            ->when(
                $validated['type'] ?? null,
                fn (Builder $query, string $type) =>
                    $query->where('type', $type)
            )
            ->when(
                $validated['category_id'] ?? null,
                fn (Builder $query, int $categoryId) =>
                    $query->where('category_id', $categoryId)
            )
            ->when(
                $validated['from'] ?? null,
                fn (Builder $query, string $from) =>
                    $query->whereDate('transaction_date', '>=', $from)
            )
            ->when(
                $validated['to'] ?? null,
                fn (Builder $query, string $to) =>
                    $query->whereDate('transaction_date', '<=', $to)
            );

        if (! empty($validated['search'])) {
            $search = $validated['search'];

            $query->where(function (Builder $query) use ($search): void {
                $query
                    ->where('description', 'like', "%{$search}%")
                    ->orWhereHas(
                        'category',
                        fn (Builder $category) =>
                            $category->where(
                                'name',
                                'like',
                                "%{$search}%"
                            )
                    );
            });
        }

        $transactions = $query
            ->latest('transaction_date')
            ->latest('id')
            ->paginate($validated['per_page'] ?? 15)
            ->withQueryString();

        return TransactionResource::collection($transactions);
    }

    public function store(
        StoreTransactionRequest $request
    ): TransactionResource {
        $transaction = $request->user()
            ->transactions()
            ->create($request->validated());

        $transaction->load('category');

        return new TransactionResource($transaction);
    }

    public function update(
        UpdateTransactionRequest $request,
        int $transaction
    ): TransactionResource {
        // Important : on récupère uniquement une transaction
        // appartenant à l'utilisateur connecté.
        $model = $request->user()
            ->transactions()
            ->findOrFail($transaction);

        $model->update($request->validated());

        $model->load('category');

        return new TransactionResource($model);
    }

    public function destroy(
        Request $request,
        int $transaction
    ): Response {
        $model = $request->user()
            ->transactions()
            ->findOrFail($transaction);

        $model->delete();

        return response()->noContent();
    }

    public function summary(Request $request)
    {
        $month = Carbon::createFromFormat(
            'Y-m',
            $request->query('month', now()->format('Y-m'))
        );

        $start = $month->copy()->startOfMonth()->toDateString();
        $end = $month->copy()->endOfMonth()->toDateString();

        $summary = $request->user()
            ->transactions()
            ->selectRaw(
                "
                COALESCE(
                    SUM(
                        CASE
                            WHEN type = 'income' THEN amount
                            ELSE -amount
                        END
                    ),
                    0
                ) AS balance,

                COALESCE(
                    SUM(
                        CASE
                            WHEN type = 'income'
                            AND transaction_date BETWEEN ? AND ?
                            THEN amount
                            ELSE 0
                        END
                    ),
                    0
                ) AS monthly_income,

                COALESCE(
                    SUM(
                        CASE
                            WHEN type = 'expense'
                            AND transaction_date BETWEEN ? AND ?
                            THEN amount
                            ELSE 0
                        END
                    ),
                    0
                ) AS monthly_expenses
                ",
                [$start, $end, $start, $end]
            )
            ->first();

        return response()->json([
            'month' => $month->format('Y-m'),

            'balance' => (string) $summary->balance,

            'monthly_income' => (string) $summary->monthly_income,

            'monthly_expenses' => (string) $summary->monthly_expenses,
        ]);
    }
}