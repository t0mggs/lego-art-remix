/**
 * Google Apps Script mejorado para VisuBloq
 * Recibe pedidos de mosaicos LEGO y los organiza en Google Sheets
 */

function doPost(e) {
  try {
    // Obtener los datos del POST
    const data = JSON.parse(e.postData.contents);
    
    // Configurar las hojas de cálculo
    const SPREADSHEET_ID = 'TU_SPREADSHEET_ID_AQUI'; // Cambia esto por tu ID
    const ss = SpreadsheetApp.openById(SPREADSHEET_ID);
    
    // Hoja principal para resúmenes de pedidos
    let resumenSheet = ss.getSheetByName('Resumen_Pedidos');
    if (!resumenSheet) {
      resumenSheet = ss.insertSheet('Resumen_Pedidos');
      // Agregar encabezados
      resumenSheet.getRange(1, 1, 1, 8).setValues([[
        'Fecha', 'ID Pedido', 'Resolución', 'Total Piezas', 'Colores Únicos', 
        'Timestamp', 'Estado', 'Ver Detalle'
      ]]);
      resumenSheet.getRange(1, 1, 1, 8).setFontWeight('bold');
    }
    
    // Hoja de detalles para cada pieza
    let detalleSheet = ss.getSheetByName('Detalle_Piezas');
    if (!detalleSheet) {
      detalleSheet = ss.insertSheet('Detalle_Piezas');
      // Agregar encabezados
      detalleSheet.getRange(1, 1, 1, 6).setValues([[
        'Fecha', 'ID Pedido', 'Color', 'Código BrickLink', 'Cantidad', 'Timestamp'
      ]]);
      detalleSheet.getRange(1, 1, 1, 6).setFontWeight('bold');
    }
    
    // Procesar los datos
    const timestamp = new Date();
    const fechaLegible = Utilities.formatDate(timestamp, Session.getScriptTimeZone(), 'dd/MM/yyyy HH:mm');
    
    // Agregar resumen del pedido
    resumenSheet.appendRow([
      fechaLegible,
      data.orderId,
      data.resolution,
      data.totalPieces,
      data.uniqueColors,
      data.timestamp,
      'Recibido',
      `=HYPERLINK("#gid=${detalleSheet.getSheetId()}&range=A:A", "Ver piezas")`
    ]);
    
    // Agregar detalle de cada pieza
    data.pieces.forEach(piece => {
      detalleSheet.appendRow([
        fechaLegible,
        data.orderId,
        piece.color,
        piece.partNumber,
        piece.quantity,
        data.timestamp
      ]);
    });
    
    // Respuesta exitosa
    return ContentService
      .createTextOutput(JSON.stringify({
        success: true,
        message: 'Pedido procesado correctamente',
        orderId: data.orderId,
        timestamp: timestamp.toISOString()
      }))
      .setMimeType(ContentService.MimeType.JSON);
      
  } catch (error) {
    // Log del error
    console.error('Error procesando pedido:', error);
    
    // Respuesta de error
    return ContentService
      .createTextOutput(JSON.stringify({
        success: false,
        error: error.toString(),
        message: 'Error al procesar el pedido'
      }))
      .setMimeType(ContentService.MimeType.JSON);
  }
}

function doGet(e) {
  // Endpoint para consultar pedidos (opcional)
  return ContentService
    .createTextOutput('VisuBloq API funcionando correctamente')
    .setMimeType(ContentService.MimeType.TEXT);
}

/**
 * Función para crear un dashboard básico
 * Ejecutar manualmente para generar estadísticas
 */
function generarDashboard() {
  const SPREADSHEET_ID = 'TU_SPREADSHEET_ID_AQUI';
  const ss = SpreadsheetApp.openById(SPREADSHEET_ID);
  
  let dashboardSheet = ss.getSheetByName('Dashboard');
  if (!dashboardSheet) {
    dashboardSheet = ss.insertSheet('Dashboard');
  }
  
  // Limpiar dashboard
  dashboardSheet.clear();
  
  // Título
  dashboardSheet.getRange(1, 1).setValue('📊 DASHBOARD VISUBLOQ');
  dashboardSheet.getRange(1, 1).setFontSize(16).setFontWeight('bold');
  
  // Estadísticas básicas
  const resumenSheet = ss.getSheetByName('Resumen_Pedidos');
  const detalleSheet = ss.getSheetByName('Detalle_Piezas');
  
  if (resumenSheet && detalleSheet) {
    const totalPedidos = resumenSheet.getLastRow() - 1; // -1 por el encabezado
    const totalPiezasUsadas = detalleSheet.getLastRow() - 1;
    
    dashboardSheet.getRange(3, 1, 4, 2).setValues([
      ['Total de Pedidos:', totalPedidos],
      ['Total de Registros de Piezas:', totalPiezasUsadas],
      ['Última Actualización:', new Date()],
      ['Estado:', 'Activo']
    ]);
  }
  
  Logger.log('Dashboard generado correctamente');
}
