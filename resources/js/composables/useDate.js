export const useDate = () => {
    const getFechaActual = () => {
        const fecha = new Date();

        const year = fecha.getFullYear();
        const month = String(fecha.getMonth() + 1).padStart(2, "0");
        const day = String(fecha.getDate()).padStart(2, "0");

        return `${year}-${month}-${day}`;
    };

    const getHoraActual = () => {
        const fecha = new Date();

        const hours = String(fecha.getHours()).padStart(2, "0");
        const minutes = String(fecha.getMinutes()).padStart(2, "0");
        const seconds = String(fecha.getSeconds()).padStart(2, "0");

        return `${hours}:${minutes}:${seconds}`;
    };

    const getFechaFormato = (formato) => {
        const fecha = new Date();

        const valores = {
            yyyy: fecha.getFullYear(),
            mm: String(fecha.getMonth() + 1).padStart(2, "0"),
            dd: String(fecha.getDate()).padStart(2, "0"),
            HH: String(fecha.getHours()).padStart(2, "0"),
            ii: String(fecha.getMinutes()).padStart(2, "0"),
            ss: String(fecha.getSeconds()).padStart(2, "0"),
        };

        return formato.replace(
            /yyyy|mm|dd|HH|ii|ss/g,
            (match) => valores[match],
        );
    };

    return {
        getFechaActual,
        getHoraActual,
        getFechaFormato,
    };
};
