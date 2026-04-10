/**
 * Извлечь сообщение об ошибке из ответа сервера.
 *
 * Поддерживает:
 *  - 422: { errors: { field: ["message"] } } — первая ошибка валидации
 *  - 401/4xx: { message: "..." }
 *  - Сетевые ошибки без response
 */
export function extractErrorMessage(error, fallback = 'Ошибка сети. Попробуйте позже.') {
    if (error.response?.data) {
        const data = error.response.data;
        if (data.errors) {
            const firstError = Object.values(data.errors)[0];
            return firstError[0];
        }
        return data.message || fallback;
    }
    return fallback;
}
