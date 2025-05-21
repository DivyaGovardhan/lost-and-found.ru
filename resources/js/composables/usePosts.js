import axios from 'axios';

export async function fetchPosts(page = 1, filters = {}) {
    const params = { page, ...filters };
    const response = await axios.get('/api/posts', { params });
    return response.data;
}


export const createPost = async (postData) => {
    const response = await axios.post('/posts', postData);
    return response.data;
};
