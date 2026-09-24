import { useForm } from "@inertiajs/vue3";

export const useCarreras = () => {
    const initialState = {
        id: 0,
        nombre: "",
        descripcion: "",
        _method: "POST",
    };

    const form = useForm({ ...initialState });

    const setCarrera = (item) => {
        form.clearErrors();
        form.reset();
        Object.assign(form, item);
        form._method = "PUT";
    };

    const limpiarCarrera = () => {
        form.clearErrors();
        form.reset();
        form.defaults({ ...initialState });
    };

    return {
        form,
        setCarrera,
        limpiarCarrera,
    };
};
