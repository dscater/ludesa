import { useForm } from "@inertiajs/vue3";

export const useTipoCertificados = () => {
    const initialState = {
        id: 0,
        nombre: "",
        precio: "",
        descripcion: "",
        _method: "POST",
    };

    const form = useForm({ ...initialState });

    const setTipoCertificado = (item) => {
        form.clearErrors();
        form.reset();
        Object.assign(form, item);
        form._method = "PUT";
    };

    const limpiarTipoCertificado = () => {
        form.clearErrors();
        form.reset();
        form.defaults({ ...initialState });
    };

    return {
        form,
        setTipoCertificado,
        limpiarTipoCertificado,
    };
};
