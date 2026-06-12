import { onMounted, onUnmounted, Ref } from 'vue';

export function useClickOutside(
    elementRef: Ref<HTMLElement | null>,
    callback: () => void = () => {},
) {
    const handleClickOutside = (event: MouseEvent) => {
        const target = event.target as HTMLElement | null;
        const element = elementRef.value;

        if (element && !element.contains(target)) {
            callback();
        }
    };

    onMounted(() => {
        document.addEventListener('click', handleClickOutside);
    });

    onUnmounted(() => {
        document.removeEventListener('click', handleClickOutside);
    });
}
