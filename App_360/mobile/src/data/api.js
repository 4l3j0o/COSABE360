/**
 * api.js - Consumo de la API externa pública de Feriados (Nager.Date)
 *
 * Cumple con los requisitos de la Jornada 4:
 * 1. Uso de fetch con async/await.
 * 2. Manejo de errores con try/catch/finally.
 * 3. Revisión obligatoria de respuesta.ok.
 * 4. Transformación de la respuesta con map y slice (primeros 12 feriados).
 */

const API_FERIADOS_URL = 'https://date.nager.at/api/v3/PublicHolidays/2026/BO';

/**
 * Obtiene los feriados nacionales oficiales de Bolivia para el año 2026.
 *
 * @returns {Promise<Array<{date: string, localName: string, name: string}>>}
 */
export async function obtenerFeriados() {
  let cargando = true;
  try {
    const respuesta = await fetch(API_FERIADOS_URL, {
      method: 'GET',
      headers: {
        Accept: 'application/json',
      },
    });

    if (!respuesta.ok) {
      throw new Error(`Error en la API pública: estado HTTP ${respuesta.status}`);
    }

    const datosCrudos = await respuesta.json();

    if (!Array.isArray(datosCrudos)) {
      throw new Error('La respuesta de la API no contiene una lista válida.');
    }

    // Transformación: se extraen únicamente los campos que la app necesita
    const feriadosFiltrados = datosCrudos
      .map((item) => ({
        date: String(item.date || ''),
        localName: String(item.localName || item.name || 'Feriado nacional'),
        name: String(item.name || ''),
      }))
      .slice(0, 12);

    return feriadosFiltrados;
  } catch (error) {
    console.error('[API Feriados] Error al consumir el servicio:', error?.message);
    throw new Error(
      error?.message || 'No se pudo conectar con el servicio de feriados. Revisa tu conexión a internet.'
    );
  } finally {
    cargando = false;
  }
}

/**
 * Convierte una fecha ISO (aaaa-mm-dd) a formato boliviano (dd/mm/aaaa).
 *
 * @param {string} iso
 * @returns {string}
 */
export function formatearFecha(iso) {
  if (!iso || typeof iso !== 'string') return '';
  const partes = iso.split('-');
  if (partes.length !== 3) return iso;
  return `${partes[2]}/${partes[1]}/${partes[0]}`;
}
