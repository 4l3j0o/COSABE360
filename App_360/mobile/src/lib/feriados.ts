import { useQuery } from '@tanstack/react-query';

import { formatearFecha, obtenerFeriados } from '@/data/api';

export type Feriado = {
  date: string;
  localName: string;
  name?: string;
};

export { formatearFecha, obtenerFeriados };

export function useFeriados() {
  return useQuery<Feriado[]>({
    queryKey: ['feriados', 2026],
    queryFn: obtenerFeriados,
    staleTime: 1000 * 60 * 60,
    retry: 1,
  });
}
