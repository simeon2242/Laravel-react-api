import { useEffect, useState } from 'react';
import { getTodos } from '../api/todoApi';

function TodoList() {
    const [todos, setTodos] = useState([]);

    useEffect(() => {
        getTodos()
            .then(response => {
                setTodos(response.data);
            })
            .catch(error => {
                console.error(error);
            });
    }, []);

    return (
        <div>
            <h1>Ma Todo List</h1>

            {todos.map(todo => (
                <div key={todo.id}>
                    <h3>{todo.title}</h3>
                    <p>{todo.description}</p>
                </div>
            ))}
        </div>
    );
}

export default TodoList;