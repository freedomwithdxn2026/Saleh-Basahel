const SHEET_NAME = 'Leads';
const WEBHOOK_SECRET = 'CHANGE_THIS_SECRET';

const HEADERS = [
  'External ID',
  'Date',
  'Name',
  'WhatsApp Number',
  'Email',
  'Country',
  'Interest In',
  'Meeting Date and Time',
  'Status',
  'Source',
  'Notes'
];

function doPost(e) {
  try {
    const payload = JSON.parse(e.postData.contents || '{}');

    if (WEBHOOK_SECRET && payload.secret !== WEBHOOK_SECRET) {
      return jsonResponse({ ok: false, error: 'unauthorized' });
    }

    const sheet = getLeadSheet();
    ensureHeader(sheet);

    const row = [
      payload.external_id || payload.whatsapp_user_id || '',
      payload.date || new Date(),
      payload.name || '',
      payload.whatsapp_number || payload.phone || '',
      payload.email || '',
      payload.country || '',
      payload.interest_in || payload.interest || '',
      payload.meeting_date_time || '',
      payload.status || 'New',
      payload.source || '',
      payload.notes || payload.message || ''
    ];

    const externalId = row[0];
    const existingRow = externalId ? findExternalIdRow(sheet, externalId) : 0;

    if (existingRow) {
      sheet.getRange(existingRow, 1, 1, HEADERS.length).setValues([row]);
    } else {
      sheet.appendRow(row);
    }

    return jsonResponse({ ok: true });
  } catch (error) {
    return jsonResponse({ ok: false, error: String(error) });
  }
}

function findExternalIdRow(sheet, externalId) {
  const lastRow = sheet.getLastRow();
  if (lastRow < 2) {
    return 0;
  }

  const values = sheet.getRange(2, 1, lastRow - 1, 1).getValues();
  for (let index = 0; index < values.length; index++) {
    if (String(values[index][0]) === String(externalId)) {
      return index + 2;
    }
  }

  return 0;
}

function getLeadSheet() {
  const spreadsheet = SpreadsheetApp.getActiveSpreadsheet();
  return spreadsheet.getSheetByName(SHEET_NAME) || spreadsheet.insertSheet(SHEET_NAME);
}

function ensureHeader(sheet) {
  const range = sheet.getRange(1, 1, 1, HEADERS.length);
  const current = range.getValues()[0];
  const isMissing = HEADERS.some((header, index) => current[index] !== header);

  if (!isMissing) {
    return;
  }

  range.setValues([HEADERS]);
  range.setFontWeight('bold');
  range.setFontColor('#ffffff');
  range.setBackground('#2f6f58');
  sheet.setFrozenRows(1);
  sheet.autoResizeColumns(1, HEADERS.length);
}

function jsonResponse(body) {
  return ContentService
    .createTextOutput(JSON.stringify(body))
    .setMimeType(ContentService.MimeType.JSON);
}
