<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Contrato de Prestación de Servicios Educativos</title>
<style>
  @page { size: A4 portrait; margin: 18mm 15mm; }
  * { font-family: DejaVu Sans, Helvetica, Arial, sans-serif; box-sizing: border-box; }
  body { font-size: 10px; color: #111; line-height: 1.5; text-align: justify; }
  h1 { font-size: 14px; text-align: center; text-transform: uppercase; margin: 0 0 2px 0; }
  h2 { font-size: 12px; text-align: center; text-transform: uppercase; margin: 0 0 2px 0; }
  h3 { font-size: 11px; text-align: center; margin: 0 0 10px 0; }
  .intro { text-align: center; margin-bottom: 10px; }
  .clausula { font-weight: bold; margin: 10px 0 4px 0; text-transform: uppercase; }
  p { margin: 3px 0; }
  table.est { width: 100%; border-collapse: collapse; margin: 6px 0; }
  table.est th, table.est td { border: 1px solid #111; padding: 4px; font-size: 9px; text-align: center; }
  .firmas { width: 100%; margin-top: 30px; }
  .firmas td { text-align: center; font-size: 9px; width: 50%; vertical-align: top; }
  .linea { border-top: 1px solid #111; margin: 40px 20px 0 20px; padding-top: 4px; }
</style>
</head>
<body>
@include('inscripciones.pdf._contrato-cuerpo')
</body>
</html>
