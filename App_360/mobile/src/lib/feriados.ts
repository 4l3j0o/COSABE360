import { useQuery } from '@tanstack/react-query';

export type Feriado = {
  date: string;
  localName: string;
};

async function obtenerFeriados(): Promise<Feriado[]> {
  const respuesta = await fetch('https://date.nager.at/api/v3/PublicHolidays/2026/BO');
  if (!respuesta.ok) {
    throw new Error(`El servidor respondió ${respuesta.status}`);
  }
  const datos = (await respuesta.json()) as Feriado[];
  return datos.map(({ date, localName }) => ({ date, localName })).slice(0, 12);
}

export function useFeriados() {
  return useQuery({
    queryKey: ['feriados', 2026],
    queryFn: obtenerFeriados,
    staleTime: 1000 * 60 * 60,
    retry: 1,
  });
}

export function formatearFecha(iso: string) {
  const [anio, mes, dia] = iso.split('-');
  return `${dia}/${mes}/${anio}`;
}
