import axios from 'axios';

const API_URL = 'http://127.0.0.1:8000/api';

export const getTodos = () => {
    return axios.get(`${API_URL}/todos`);
};

export const getTodo = (id) => {
    return axios.get(`${API_URL}/todos/${id}`);
};

export const createTodo = (todo) => {
    return axios.post(`${API_URL}/todos`, todo);
};

export const updateTodo = (id, todo) => {
    return axios.put(`${API_URL}/todos/${id}`, todo);
};

export const deleteTodo = (id) => {
    return axios.delete(`${API_URL}/todos/${id}`);
};