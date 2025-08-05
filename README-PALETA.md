# Sistema de Paleta de Colores Fija - VisuBloq

## Descripción
El sistema ahora utiliza una paleta de colores fija que es controlada únicamente por el administrador. Los usuarios no pueden modificar, agregar o eliminar colores de la paleta.

## Archivo de Paleta
**Ubicación:** `assets/fixed-palette.json`

### Estructura del archivo JSON:
```json
{
  "name": "Nombre de la paleta",
  "version": "Versión",
  "description": "Descripción de la paleta",
  "studMap": {
    "#FFFFFF": 1000,
    "#000000": 1000,
    "#FF0000": 500
  },
  "sortedStuds": [
    "#FFFFFF",
    "#000000", 
    "#FF0000"
  ]
}
```

### Campos explicados:
- **studMap**: Objeto donde las claves son colores en formato hexadecimal (#RRGGBB) y los valores son la cantidad disponible de cada color
- **sortedStuds**: Array con los colores en el orden que aparecerán en la interfaz

## Modificar la Paleta

### 1. Para agregar un nuevo color:
- Añádelo al objeto `studMap` con su cantidad
- Inclúyelo en el array `sortedStuds` en la posición deseada

### 2. Para cambiar cantidades:
- Modifica el valor numérico en el objeto `studMap`

### 3. Para eliminar un color:
- Elimínalo tanto del `studMap` como del array `sortedStuds`

### 4. Para reordenar colores:
- Cambia el orden en el array `sortedStuds`

## Ejemplo de modificación:
```json
{
  "name": "Paleta Actualizada VisuBloq",
  "version": "1.1.0", 
  "description": "Paleta optimizada para mejores resultados",
  "studMap": {
    "#FFFFFF": 2000,
    "#000000": 1500,
    "#FF0000": 800,
    "#00FF00": 600,
    "#0000FF": 600,
    "#FFFF00": 400
  },
  "sortedStuds": [
    "#FFFFFF",
    "#000000",
    "#FF0000",
    "#00FF00",
    "#0000FF", 
    "#FFFF00"
  ]
}
```

## Notas importantes:
- Los colores deben estar en formato hexadecimal (#RRGGBB)
- Las cantidades deben ser números enteros positivos
- Después de modificar el archivo, los usuarios verán los cambios al recargar la página
- La paleta se carga automáticamente al iniciar la aplicación
- En caso de error al cargar la paleta, se usará la paleta por defecto

## Ventajas del sistema:
- Control total sobre los colores disponibles
- Fácil actualización sin tocar código
- Consistencia para todos los usuarios
- Optimización comercial (control de inventario virtual)
