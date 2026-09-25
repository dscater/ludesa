import { useForm } from "@inertiajs/vue3";
import { useDate } from "../useDate";

export const useCampeonatoInscripcions = () => {
    const initialState = {
        id: 0,
        campeonato_id: "",
        campeonato: null,
        carrera_id: "",
        pj: "",
        pts: "",
        gf: "",
        gc: "",
        dg: "",
        pg: "",
        pe: "",
        pp: "",
        estado: "",
        fecha: useDate().getFechaActual(),
        hora: useDate().getHoraActual(),
        _method: "POST",
    };

    const form = useForm({ ...initialState });

    const setCampeonatoInscripcion = (item) => {
        form.clearErrors();
        form.reset();
        Object.assign(form, item);
        form._method = "PUT";
    };

    const limpiarCampeonatoInscripcion = () => {
        form.clearErrors();
        form.reset();
        form.defaults({ ...initialState });
    };

    return {
        form,
        setCampeonatoInscripcion,
        limpiarCampeonatoInscripcion,
    };
};
