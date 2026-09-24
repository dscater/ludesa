import { useForm } from "@inertiajs/vue3";

export const useCampeonatos = () => {
    const initialState = {
        id: 0,
        nombre: "",
        periodo: "",
        gestion: "",
        tipo: "",
        descripcion: "",
        estado: "",
        _method: "POST",
    };

    const form = useForm({ ...initialState });

    const setCampeonato = (item) => {
        form.clearErrors();
        form.reset();
        Object.assign(form, item);
        form._method = "PUT";
    };

    const limpiarCampeonato = () => {
        form.clearErrors();
        form.reset();
        form.defaults({ ...initialState });
    };

    return {
        form,
        setCampeonato,
        limpiarCampeonato,
    };
};
