import { ref, Component, markRaw } from 'vue';

export interface DialogOptions {
    component: Component;
    props?: Record<string, any>;
}

interface DialogState {
    isOpen: boolean;
    component?: Component;
    props?: Record<string, any>;
    key?: string;
}

const dialogState = ref<DialogState>({
    isOpen: false,
    component: undefined,
    props: {},
    key: '',
});

export function useDialog() {
    const open = (options: DialogOptions) => {
        dialogState.value = {
            isOpen: true,
            component: markRaw(options.component),
            props: options.props || {},
            key: crypto.randomUUID(),
        };
    };

    const close = () => {
        dialogState.value.isOpen = false;
    };

    return {
        dialogState,
        open,
        close,
    };
}
