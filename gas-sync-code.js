/**
 * 助成金投稿 - WordPress & Spreadsheet 完全連携システム
 * Google Apps Script Code
 * 
 * 機能:
 * - スプレッドシート ↔ WordPress 双方向同期
 * - 自動削除・公開状態同期
 * - リアルタイム更新
 * - 完全なフィールドマッピング
 */

// ================================
// 設定 (Configuration)
// ================================
const CONFIG = {
  SHEET_NAME: 'grant_import',
  WP_SITE_URL: PropertiesService.getScriptProperties().getProperty('WP_SITE_URL') || 'https://joseikin-insight.com',
  WP_USERNAME: PropertiesService.getScriptProperties().getProperty('WP_USERNAME') || 'keishiadmin',
  WP_APP_PASSWORD: PropertiesService.getScriptProperties().getProperty('WP_APP_PASSWORD') || '',
  
  // 同期設定
  SYNC_INTERVAL_MINUTES: 5, // 自動同期間隔（分）
  MAX_RETRIES: 3,           // リトライ回数
  BATCH_SIZE: 50,           // バッチサイズ
  
  // WordPressカスタムフィールドマッピング
  CUSTOM_FIELDS: {
    organization: 'B',           // 実施組織
    organization_type: 'C',      // 組織タイプ
    max_amount: 'D',            // 最大金額
    min_amount: 'E',            // 最小金額
    max_grant_amount: 'F',      // 最大助成額（数値）
    subsidy_rate: 'G',          // 補助率
    amount_notes: 'H',          // 金額備考
    application_deadline: 'I',   // 申請期限
    recruitment_start: 'J',      // 募集開始日
    deadline_date: 'K',         // 締切日
    deadline_notes: 'L',        // 締切備考
    application_status: 'M',     // 申請ステータス
    target_prefecture: 'N',      // 対象都道府県
    target_municipality: 'O',    // 対象市町村
    regional_limitation: 'P',    // 地域制限
    region_notes: 'Q',          // 地域備考
    grant_category: 'R',        // カテゴリー
    grant_tags: 'S',            // タグ
    grant_target: 'T',          // 助成金対象
    eligible_expenses: 'U',      // 対象経費
    difficulty: 'V',            // 難易度
    success_rate: 'W',          // 成功率
    eligibility_requirements: 'X', // 対象者・応募要件
    application_procedure: 'Y',   // 申請手順
    application_method: 'Z',     // 申請方法
    required_documents: 'AA',    // 必要書類
    contact_info: 'BB',         // 連絡先情報
    official_url: 'CC',         // 公式URL
    excerpt: 'DD',              // 概要
    is_featured: 'EE'           // 注目の助成金
  }
};

// ================================
// スプレッドシートヘッダー設定
// ================================
const SPREADSHEET_HEADERS = [
  'ID',                    // A
  'タイトル',              // B
  '実施組織',              // C
  '組織タイプ',            // D
  '最大金額（万円）',      // E
  '最小金額（万円）',      // F
  '最大助成額（数値・円単位）', // G
  '補助率（%）',          // H
  '金額備考',              // I
  '申請期限',              // J
  '募集開始日',            // K
  '締切日',               // L
  '締切に関する備考',      // M
  '申請ステータス',        // N
  '対象都道府県',          // O
  '対象市町村',            // P
  '地域制限',              // Q
  '地域に関する備考',      // R
  'カテゴリー',            // S
  'タグ',                 // T
  '助成金対象',            // U
  '対象経費',              // V
  '難易度',               // W
  '成功率（%）',          // X
  '対象者・応募要件',      // Y
  '申請手順',              // Z
  '申請方法',              // AA
  '必要書類',              // BB
  '連絡先情報',            // CC
  '公式URL',              // DD
  '概要',                 // EE
  '本文内容',              // FF
  '注目の助成金',          // GG
  'ステータス',            // HH (draft/publish/trash)
  '作成日',               // II
  '更新日',               // JJ
  '同期状態'              // KK (synced/pending/error)
];

// ================================
// メイン実行関数
// ================================

/**
 * 手動同期実行
 */
function manualSync() {
  try {
    Logger.log('手動同期開始...');
    const results = performBidirectionalSync();
    Logger.log(`同期完了: ${JSON.stringify(results)}`);
    
    // 結果をスプレッドシートに表示
    showSyncResults(results);
  } catch (error) {
    Logger.log(`手動同期エラー: ${error.message}`);
    showError('手動同期でエラーが発生しました: ' + error.message);
  }
}

/**
 * 自動同期実行（トリガー用）
 */
function autoSync() {
  try {
    Logger.log('自動同期開始...');
    const results = performBidirectionalSync();
    Logger.log(`自動同期完了: ${JSON.stringify(results)}`);
  } catch (error) {
    Logger.log(`自動同期エラー: ${error.message}`);
    // エラー通知（必要に応じてメール送信等）
    sendErrorNotification(error);
  }
}

/**
 * 双方向同期処理
 */
function performBidirectionalSync() {
  const sheet = getGrantSheet();
  
  // 1. スプレッドシート → WordPress 同期
  const wpSyncResults = syncSpreadsheetToWordPress(sheet);
  
  // 2. WordPress → スプレッドシート 同期
  const sheetSyncResults = syncWordPressToSpreadsheet(sheet);
  
  return {
    wordpressSync: wpSyncResults,
    spreadsheetSync: sheetSyncResults,
    timestamp: new Date().toISOString()
  };
}

// ================================
// スプレッドシート → WordPress 同期
// ================================

/**
 * スプレッドシートの変更をWordPressに同期
 */
function syncSpreadsheetToWordPress(sheet) {
  const data = sheet.getDataRange().getValues();
  const headers = data[0];
  const rows = data.slice(1);
  
  let created = 0, updated = 0, deleted = 0, errors = [];
  
  for (let i = 0; i < rows.length; i++) {
    const rowIndex = i + 2; // スプレッドシートの行番号
    const row = rows[i];
    
    try {
      const postId = row[0]; // A列: ID
      const title = row[1];  // B列: タイトル
      const status = row[getColumnIndex('ステータス')]; // HH列: ステータス
      const syncState = row[getColumnIndex('同期状態')]; // KK列: 同期状態
      
      // 空行をスキップ
      if (!title || title.trim() === '') {
        continue;
      }
      
      if (status === 'trash' || status === '削除') {
        // 削除処理
        if (postId && postId !== '') {
          const deleteResult = deleteWordPressPost(postId);
          if (deleteResult.success) {
            deleted++;
            // スプレッドシートから行を削除
            sheet.deleteRow(rowIndex);
          } else {
            errors.push(`行${rowIndex}: 削除エラー - ${deleteResult.error}`);
          }
        }
      } else if (syncState === 'pending' || syncState === 'error' || syncState === '') {
        // 作成・更新処理
        const postData = buildPostDataFromRow(row, headers);
        
        if (postId && postId !== '') {
          // 更新
          const updateResult = updateWordPressPost(postId, postData);
          if (updateResult.success) {
            updated++;
            sheet.getRange(rowIndex, getColumnIndex('同期状態') + 1).setValue('synced');
            sheet.getRange(rowIndex, getColumnIndex('更新日') + 1).setValue(new Date());
          } else {
            errors.push(`行${rowIndex}: 更新エラー - ${updateResult.error}`);
            sheet.getRange(rowIndex, getColumnIndex('同期状態') + 1).setValue('error');
          }
        } else {
          // 新規作成
          const createResult = createWordPressPost(postData);
          if (createResult.success) {
            created++;
            sheet.getRange(rowIndex, 1).setValue(createResult.id); // ID列にWordPress投稿IDを設定
            sheet.getRange(rowIndex, getColumnIndex('同期状態') + 1).setValue('synced');
            sheet.getRange(rowIndex, getColumnIndex('作成日') + 1).setValue(new Date());
          } else {
            errors.push(`行${rowIndex}: 作成エラー - ${createResult.error}`);
            sheet.getRange(rowIndex, getColumnIndex('同期状態') + 1).setValue('error');
          }
        }
      }
      
    } catch (error) {
      errors.push(`行${rowIndex}: 処理エラー - ${error.message}`);
    }
  }
  
  return { created, updated, deleted, errors };
}

/**
 * スプレッドシートの行データからWordPress投稿データを構築
 */
function buildPostDataFromRow(row, headers) {
  const postData = {
    title: row[1] || '', // B列: タイトル
    content: row[getColumnIndex('本文内容')] || '', // FF列: 本文内容
    excerpt: row[getColumnIndex('概要')] || '', // EE列: 概要
    status: normalizePostStatus(row[getColumnIndex('ステータス')] || 'draft'), // HH列: ステータス
    meta: {}
  };
  
  // カスタムフィールドをマッピング
  Object.keys(CONFIG.CUSTOM_FIELDS).forEach(fieldName => {
    const columnLetter = CONFIG.CUSTOM_FIELDS[fieldName];
    const columnIndex = getColumnIndex(columnLetter);
    const value = row[columnIndex];
    
    if (value !== undefined && value !== '') {
      postData.meta[fieldName] = String(value);
    }
  });
  
  // タクソノミーの処理
  postData.grant_category = parseCommaSeparated(row[getColumnIndex('カテゴリー')] || '');
  postData.grant_prefecture = parseCommaSeparated(row[getColumnIndex('対象都道府県')] || '');
  postData.grant_tag = parseCommaSeparated(row[getColumnIndex('タグ')] || '');
  
  return postData;
}

// ================================
// WordPress → スプレッドシート 同期
// ================================

/**
 * WordPressの投稿をスプレッドシートに同期
 */
function syncWordPressToSpreadsheet(sheet) {
  const wordpressPosts = getAllWordPressPosts();
  const sheetData = sheet.getDataRange().getValues();
  const existingIds = sheetData.slice(1).map(row => String(row[0])).filter(id => id !== '');
  
  let created = 0, updated = 0, errors = [];
  
  wordpressPosts.forEach(post => {
    try {
      const postId = String(post.id);
      const existingRowIndex = findRowByPostId(sheet, postId);
      
      if (existingRowIndex > 0) {
        // 既存の行を更新
        updateSpreadsheetRow(sheet, existingRowIndex, post);
        updated++;
      } else {
        // 新しい行を追加
        addSpreadsheetRow(sheet, post);
        created++;
      }
    } catch (error) {
      errors.push(`投稿ID ${post.id}: ${error.message}`);
    }
  });
  
  return { created, updated, errors };
}

/**
 * WordPress投稿データでスプレッドシート行を更新
 */
function updateSpreadsheetRow(sheet, rowIndex, post) {
  const rowData = buildRowDataFromPost(post);
  const range = sheet.getRange(rowIndex, 1, 1, rowData.length);
  range.setValues([rowData]);
}

/**
 * WordPress投稿データで新しいスプレッドシート行を追加
 */
function addSpreadsheetRow(sheet, post) {
  const rowData = buildRowDataFromPost(post);
  sheet.appendRow(rowData);
}

/**
 * WordPress投稿データからスプレッドシート行データを構築
 */
function buildRowDataFromPost(post) {
  const rowData = new Array(SPREADSHEET_HEADERS.length);
  
  // 基本データ
  rowData[0] = post.id;
  rowData[1] = post.title.rendered || '';
  rowData[getColumnIndex('本文内容')] = stripHtmlTags(post.content.rendered || '');
  rowData[getColumnIndex('概要')] = stripHtmlTags(post.excerpt.rendered || '');
  rowData[getColumnIndex('ステータス')] = post.status || 'draft';
  rowData[getColumnIndex('作成日')] = new Date(post.date);
  rowData[getColumnIndex('更新日')] = new Date(post.modified);
  rowData[getColumnIndex('同期状態')] = 'synced';
  
  // カスタムフィールドデータ
  if (post.meta) {
    Object.keys(CONFIG.CUSTOM_FIELDS).forEach(fieldName => {
      const columnLetter = CONFIG.CUSTOM_FIELDS[fieldName];
      const columnIndex = getColumnIndex(columnLetter);
      rowData[columnIndex] = post.meta[fieldName] || '';
    });
  }
  
  // タクソノミーデータ
  if (post.grant_category) {
    rowData[getColumnIndex('カテゴリー')] = post.grant_category.join(', ');
  }
  if (post.grant_prefecture) {
    rowData[getColumnIndex('対象都道府県')] = post.grant_prefecture.join(', ');
  }
  if (post.grant_tag) {
    rowData[getColumnIndex('タグ')] = post.grant_tag.join(', ');
  }
  
  return rowData;
}

// ================================
// WordPress API 関数
// ================================

/**
 * WordPress投稿を作成
 */
function createWordPressPost(postData) {
  try {
    const url = `${CONFIG.WP_SITE_URL}/wp-json/wp/v2/grant`;
    const options = {
      method: 'POST',
      headers: {
        'Authorization': 'Basic ' + Utilities.base64Encode(`${CONFIG.WP_USERNAME}:${CONFIG.WP_APP_PASSWORD}`),
        'Content-Type': 'application/json'
      },
      payload: JSON.stringify(postData)
    };
    
    const response = UrlFetchApp.fetch(url, options);
    
    if (response.getResponseCode() === 201) {
      const responseData = JSON.parse(response.getContentText());
      return { success: true, id: responseData.id };
    } else {
      return { 
        success: false, 
        error: `HTTP ${response.getResponseCode()}: ${response.getContentText()}` 
      };
    }
  } catch (error) {
    return { success: false, error: error.message };
  }
}

/**
 * WordPress投稿を更新
 */
function updateWordPressPost(postId, postData) {
  try {
    const url = `${CONFIG.WP_SITE_URL}/wp-json/wp/v2/grant/${postId}`;
    const options = {
      method: 'POST',
      headers: {
        'Authorization': 'Basic ' + Utilities.base64Encode(`${CONFIG.WP_USERNAME}:${CONFIG.WP_APP_PASSWORD}`),
        'Content-Type': 'application/json'
      },
      payload: JSON.stringify(postData)
    };
    
    const response = UrlFetchApp.fetch(url, options);
    
    if (response.getResponseCode() === 200) {
      return { success: true };
    } else {
      return { 
        success: false, 
        error: `HTTP ${response.getResponseCode()}: ${response.getContentText()}` 
      };
    }
  } catch (error) {
    return { success: false, error: error.message };
  }
}

/**
 * WordPress投稿を削除
 */
function deleteWordPressPost(postId) {
  try {
    const url = `${CONFIG.WP_SITE_URL}/wp-json/wp/v2/grant/${postId}?force=true`;
    const options = {
      method: 'DELETE',
      headers: {
        'Authorization': 'Basic ' + Utilities.base64Encode(`${CONFIG.WP_USERNAME}:${CONFIG.WP_APP_PASSWORD}`)
      }
    };
    
    const response = UrlFetchApp.fetch(url, options);
    
    if (response.getResponseCode() === 200) {
      return { success: true };
    } else {
      return { 
        success: false, 
        error: `HTTP ${response.getResponseCode()}: ${response.getContentText()}` 
      };
    }
  } catch (error) {
    return { success: false, error: error.message };
  }
}

/**
 * すべてのWordPress投稿を取得
 */
function getAllWordPressPosts() {
  const allPosts = [];
  let page = 1;
  const perPage = 100;
  
  try {
    while (true) {
      const url = `${CONFIG.WP_SITE_URL}/wp-json/wp/v2/grant?per_page=${perPage}&page=${page}&_embed`;
      const options = {
        method: 'GET',
        headers: {
          'Authorization': 'Basic ' + Utilities.base64Encode(`${CONFIG.WP_USERNAME}:${CONFIG.WP_APP_PASSWORD}`)
        }
      };
      
      const response = UrlFetchApp.fetch(url, options);
      
      if (response.getResponseCode() !== 200) {
        break;
      }
      
      const posts = JSON.parse(response.getContentText());
      
      if (posts.length === 0) {
        break;
      }
      
      allPosts.push(...posts);
      page++;
    }
  } catch (error) {
    Logger.log(`WordPress投稿取得エラー: ${error.message}`);
  }
  
  return allPosts;
}

// ================================
// ユーティリティ関数
// ================================

/**
 * スプレッドシートを取得
 */
function getGrantSheet() {
  const spreadsheet = SpreadsheetApp.getActiveSpreadsheet();
  let sheet = spreadsheet.getSheetByName(CONFIG.SHEET_NAME);
  
  if (!sheet) {
    sheet = spreadsheet.insertSheet(CONFIG.SHEET_NAME);
    initializeSheet(sheet);
  }
  
  return sheet;
}

/**
 * スプレッドシートを初期化
 */
function initializeSheet(sheet) {
  // ヘッダー行を設定
  sheet.getRange(1, 1, 1, SPREADSHEET_HEADERS.length).setValues([SPREADSHEET_HEADERS]);
  
  // ヘッダーのスタイル設定
  const headerRange = sheet.getRange(1, 1, 1, SPREADSHEET_HEADERS.length);
  headerRange.setBackground('#4285f4');
  headerRange.setFontColor('#ffffff');
  headerRange.setFontWeight('bold');
  headerRange.setHorizontalAlignment('center');
  
  // 列幅の調整
  sheet.autoResizeColumns(1, SPREADSHEET_HEADERS.length);
  
  Logger.log('スプレッドシートを初期化しました');
}

/**
 * 列インデックスを取得
 */
function getColumnIndex(columnLetterOrName) {
  if (typeof columnLetterOrName === 'number') {
    return columnLetterOrName;
  }
  
  // 列名からインデックスを検索
  const index = SPREADSHEET_HEADERS.indexOf(columnLetterOrName);
  if (index >= 0) {
    return index;
  }
  
  // 列文字（A, B, C...）からインデックスを計算
  if (typeof columnLetterOrName === 'string') {
    let result = 0;
    for (let i = 0; i < columnLetterOrName.length; i++) {
      result = result * 26 + (columnLetterOrName.charCodeAt(i) - 64);
    }
    return result - 1;
  }
  
  return 0;
}

/**
 * 投稿IDから行を検索
 */
function findRowByPostId(sheet, postId) {
  const data = sheet.getDataRange().getValues();
  
  for (let i = 1; i < data.length; i++) {
    if (String(data[i][0]) === String(postId)) {
      return i + 1; // スプレッドシートの行番号
    }
  }
  
  return -1;
}

/**
 * 投稿ステータスを正規化
 */
function normalizePostStatus(status) {
  const statusMap = {
    '下書き': 'draft',
    '公開': 'publish',
    '非公開': 'private',
    '削除': 'trash',
    'draft': 'draft',
    'publish': 'publish',
    'private': 'private',
    'trash': 'trash'
  };
  
  return statusMap[status] || 'draft';
}

/**
 * カンマ区切り文字列をパース
 */
function parseCommaSeparated(str) {
  if (!str || str.trim() === '') {
    return [];
  }
  
  return str.split(',').map(item => item.trim()).filter(item => item !== '');
}

/**
 * HTMLタグを削除
 */
function stripHtmlTags(html) {
  return html.replace(/<[^>]*>/g, '').replace(/\s+/g, ' ').trim();
}

/**
 * 同期結果を表示
 */
function showSyncResults(results) {
  const message = `
同期完了！

WordPress同期:
- 作成: ${results.wordpressSync.created}件
- 更新: ${results.wordpressSync.updated}件  
- 削除: ${results.wordpressSync.deleted}件
- エラー: ${results.wordpressSync.errors.length}件

スプレッドシート同期:
- 作成: ${results.spreadsheetSync.created}件
- 更新: ${results.spreadsheetSync.updated}件
- エラー: ${results.spreadsheetSync.errors.length}件

時刻: ${results.timestamp}
  `;
  
  SpreadsheetApp.getUi().alert('同期結果', message, SpreadsheetApp.getUi().ButtonSet.OK);
}

/**
 * エラー表示
 */
function showError(message) {
  SpreadsheetApp.getUi().alert('エラー', message, SpreadsheetApp.getUi().ButtonSet.OK);
}

/**
 * エラー通知送信
 */
function sendErrorNotification(error) {
  // 必要に応じてメール通知等を実装
  Logger.log(`同期エラー通知: ${error.message}`);
}

// ================================
// トリガー設定
// ================================

/**
 * 自動同期トリガーを設定
 */
function setupAutoSyncTrigger() {
  // 既存のトリガーを削除
  deleteAllTriggers();
  
  // 新しいトリガーを作成（指定間隔で実行）
  ScriptApp.newTrigger('autoSync')
    .timeBased()
    .everyMinutes(CONFIG.SYNC_INTERVAL_MINUTES)
    .create();
    
  // スプレッドシート変更時トリガー
  ScriptApp.newTrigger('onSpreadsheetEdit')
    .onEdit()
    .create();
    
  Logger.log('自動同期トリガーを設定しました');
}

/**
 * すべてのトリガーを削除
 */
function deleteAllTriggers() {
  const triggers = ScriptApp.getProjectTriggers();
  triggers.forEach(trigger => ScriptApp.deleteTrigger(trigger));
}

/**
 * スプレッドシート編集時の処理
 */
function onSpreadsheetEdit(event) {
  const range = event.range;
  const sheet = range.getSheet();
  
  // grant_importシートの変更のみ処理
  if (sheet.getName() !== CONFIG.SHEET_NAME) {
    return;
  }
  
  // ヘッダー行の変更は無視
  if (range.getRow() === 1) {
    return;
  }
  
  // 同期状態を「pending」に設定
  const syncStateColumn = getColumnIndex('同期状態') + 1;
  sheet.getRange(range.getRow(), syncStateColumn).setValue('pending');
}

// ================================
// セットアップ関数
// ================================

/**
 * 初期セットアップ
 */
function setupSync() {
  try {
    // スプレッドシートの初期化
    const sheet = getGrantSheet();
    Logger.log('スプレッドシート初期化完了');
    
    // トリガーの設定
    setupAutoSyncTrigger();
    Logger.log('トリガー設定完了');
    
    // 初回同期実行
    const results = performBidirectionalSync();
    Logger.log('初回同期完了');
    
    SpreadsheetApp.getUi().alert(
      'セットアップ完了',
      'WordPress連携が正常に設定されました。\n自動同期が開始されます。',
      SpreadsheetApp.getUi().ButtonSet.OK
    );
    
  } catch (error) {
    Logger.log(`セットアップエラー: ${error.message}`);
    showError('セットアップでエラーが発生しました: ' + error.message);
  }
}

/**
 * 設定を確認
 */
function checkConfiguration() {
  const properties = PropertiesService.getScriptProperties().getProperties();
  
  const requiredProperties = ['WP_SITE_URL', 'WP_USERNAME', 'WP_APP_PASSWORD'];
  const missingProperties = [];
  
  requiredProperties.forEach(prop => {
    if (!properties[prop]) {
      missingProperties.push(prop);
    }
  });
  
  if (missingProperties.length > 0) {
    showError(`以下の設定が不足しています:\n${missingProperties.join('\n')}`);
    return false;
  }
  
  return true;
}

// ================================
// メニュー設定
// ================================

/**
 * スプレッドシートメニューを作成
 */
function onOpen() {
  const ui = SpreadsheetApp.getUi();
  
  ui.createMenu('WordPress連携')
    .addItem('初期セットアップ', 'setupSync')
    .addItem('手動同期実行', 'manualSync')
    .addItem('設定確認', 'checkConfiguration')
    .addSeparator()
    .addItem('自動同期開始', 'setupAutoSyncTrigger')
    .addItem('自動同期停止', 'deleteAllTriggers')
    .addToUi();
}