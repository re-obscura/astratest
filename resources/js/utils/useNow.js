/**
 * Composable: useNow
 *
 * Возвращает единственный на всё приложение реактивный ref `now`,
 * который обновляется раз в 10 секунд через один общий setInterval.
 * Все компоненты TaskCard подписываются на один и тот же ref,
 * вместо того чтобы каждый заводить собственный таймер.
 */
import { ref, onUnmounted } from 'vue';

const now = ref(new Date());
let refCount = 0;
let intervalId = null;

export function useNow() {
    if (refCount === 0) {
        intervalId = setInterval(() => {
            now.value = new Date();
        }, 10000);
    }
    refCount++;

    onUnmounted(() => {
        refCount--;
        if (refCount === 0 && intervalId !== null) {
            clearInterval(intervalId);
            intervalId = null;
        }
    });

    return { now };
}
