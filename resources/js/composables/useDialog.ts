import { ref, Component, markRaw } from 'vue';

export interface DialogOptions {
    component: Component;
    props?: Record<string, any>;
}

interface DialogState {
    isOpen: boolean;
    component?: Component;
    props?: Record<string, any>;
}

const dialogState = ref<DialogState>({
    isOpen: false,
    component: undefined,
    props: {},
});

export function useDialog() {
    const open = (options: DialogOptions) => {
        dialogState.value = {
            isOpen: true,
            component: markRaw(options.component),
            props: options.props || {},
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
