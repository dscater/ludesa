import { useForm } from "@inertiajs/vue3";
import { useDate } from "../useDate";

export const usePartidos = () => {
    const initialState = {
        id: 0,
        campeonato_id: "",
        ci_local_id: "",
        local_id: "",
        ci_visitante_id: "",
        visitante_id: "",
        goles_local: "",
        goles_visitante: "",
        ganador_id: "",
        total_local: "",
        pago_local: "",
        total_visitante: "",
        pago_visitante: "",
        estado: "",
        fecha: useDate().getFechaActual(),
        hora: useDate().getHoraActual(),
        _method: "POST",
    };

    const form = useForm({ ...initialState });

    const setPartido = (item) => {
        form.clearErrors();
        form.reset();
        Object.assign(form, item);
        form._method = "PUT";
    };

    const limpiarPartido = () => {
        form.clearErrors();
        form.reset();
        form.defaults({ ...initialState });
    };

    return {
        form,
        setPartido,
        limpiarPartido,
    };
};
