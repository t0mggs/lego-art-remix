// OVERRIDE FINAL PARA VISUBLOQ - SOLO USAR ESTOS 16 COLORES
console.log("Cargando override de paleta VisuBloq...");

// Definir EXACTAMENTE los 16 colores que deben usarse
const VISUBLOQ_COLORS = [
    "#212121", // Negro
    "#0057a6", // Azul
    "#10cb31", // Verde
    "#f7ba30", // Amarillo/Naranja
    "#f88379", // Rosa/Salmón
    "#3399ff", // Azul claro
    "#595d60", // Gris oscuro
    "#b30006", // Rojo
    "#7c9051", // Verde oliva
    "#fffc00", // Amarillo brillante
    "#89351d", // Marrón
    "#907450", // Marrón claro
    "#898788", // Gris
    "#ffbbff", // Rosa claro
    "#e3a05b", // Beige/Arena
    "#ffffff"  // Blanco
];

// Crear el mapa de colores con 99999 piezas cada uno
const VISUBLOQ_STUD_MAP = {};
VISUBLOQ_COLORS.forEach(color => {
    VISUBLOQ_STUD_MAP[color] = 99999;
});

// SOBRESCRIBIR COMPLETAMENTE todas las variables de colores
window.addEventListener('load', function() {
    console.log("Aplicando override final de paleta VisuBloq...");
    
    // Reemplazar completamente STUD_MAPS
    window.STUD_MAPS = {
        "visubloq_default": {
            name: "VisuBloq Default Palette",
            officialName: "VisuBloq Default Palette",
            sortedStuds: VISUBLOQ_COLORS,
            studMap: VISUBLOQ_STUD_MAP,
        }
    };
    
    // Reemplazar completamente ALL_VALID_BRICKLINK_COLORS
    window.ALL_VALID_BRICKLINK_COLORS = VISUBLOQ_COLORS.map(color => ({
        name: color,
        hex: color
    }));
    
    window.ALL_BRICKLINK_SOLID_COLORS = window.ALL_VALID_BRICKLINK_COLORS;
    
    // Forzar variables globales
    window.DEFAULT_STUD_MAP = "visubloq_default";
    window.DEFAULT_COLOR = VISUBLOQ_COLORS[0];
    window.DEFAULT_COLOR_NAME = VISUBLOQ_COLORS[0];
    
    // Variables de override
    window.FORCE_PALETTE_COLORS = VISUBLOQ_COLORS;
    window.FORCE_PALETTE_MAP = VISUBLOQ_STUD_MAP;
    
    console.log("Override aplicado. Colores disponibles:", VISUBLOQ_COLORS);
    console.log("STUD_MAPS override:", window.STUD_MAPS);
});
