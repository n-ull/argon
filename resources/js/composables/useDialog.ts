import { ref, Component, markRaw } from 'vue';

export interface DialogOptions {
    title?: string;
    description?: string;
    component?: Component;
    props?: Record<string, any>;
    onConfirm?: () => void | Promise<void>;
    onCancel?: () => void;
    confirmText?: string;
    cancelText?: string;
    showCancel?: boolean;
}

interface DialogState extends DialogOptions {
    isOpen: boolean;
}

const dialogState = ref<DialogState>({
    isOpen: false,
    title: '',
    description: '',
    component: undefined,
    props: {},
    onConfirm: undefined,
    onCancel: undefined,
    confirmText: 'Confirm',
    cancelText: 'Cancel',
    showCancel: true,
});

export function useDialog() {
    const open = (options: DialogOptions) => {
        dialogState.value = {
            isOpen: true,
            title: options.title || '',
            description: options.description || '',
            component: options.component ? markRaw(options.component) : undefined,
            props: options.props || {},
            onConfirm: options.onConfirm,
            onCancel: options.onCancel,
            confirmText: options.confirmText || 'Confirm',
            cancelText: options.cancelText || 'Cancel',
            showCancel: options.showCancel !== undefined ? options.showCancel : true,
        };
    };

    const close = () => {
        dialogState.value.isOpen = false;
    };

    const confirm = async () => {
        if (dialogState.value.onConfirm) {
            await dialogState.value.onConfirm();
        }
        close();
    };

    const cancel = () => {
        if (dialogState.value.onCancel) {
            dialogState.value.onCancel();
        }
        close();
    };

    return {
        dialogState,
        open,
        close,
        confirm,
        cancel,
    };
}
