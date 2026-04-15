/**
 * Утилиты для форматирования дат.
 */

/**
 * Форматировать ISO-строку даты в читаемый вид на русском языке.
 * @param {string} isoStr
 * @returns {string}
 */
export function formatDatetime(isoStr) {
    if (!isoStr) return '';
    return new Date(isoStr).toLocaleString('ru-RU', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
}

/**
 * Преобразовать ISO-строку в значение для input[type="datetime-local"].
 * Формат: YYYY-MM-DDTHH:mm в локальном часовом поясе.
 * @param {string} isoStr
 * @returns {string}
 */
export function toDatetimeLocalValue(isoStr) {
    if (!isoStr) return '';
    const d = new Date(isoStr);
    const pad = (n) => n.toString().padStart(2, '0');
    return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`;
}
