<main class="main flex-fill animate-fadeIn" id="main">
    <div class="container-fluid py-4">
        <div class="row mb-4">
            <div class="col-lg-12">
                <h1 class="page-title">시스템 로그</h1>
                <p class="text-muted">인플루언서 솔루션의 시스템 로그를 확인할 수 있습니다.</p>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="card-title m-0">로그 목록</h5>
                <div>
                    <button type="button" class="btn btn-outline-secondary" id="btnExportCSV">
                        <i class="bi bi-file-earmark-excel me-1"></i> CSV 내보내기
                    </button>
                </div>
            </div>
            <div class="card-body">
                <!-- 검색 필터 -->
                <div class="row mb-4">
                    <div class="col-md-12">
                        <form id="searchForm" class="d-flex gap-2">
                            <select name="type" class="form-select" style="width: 150px;">
                                <option value="">모든 유형</option>
                                <option value="DEFAULT">기본</option>
                                <option value="API">API</option>
                                <option value="SYSTEM">시스템</option>
                                <option value="USER">사용자</option>
                                <option value="INFLUENCER">인플루언서</option>
                                <option value="AUTH">인증</option>
                                <option value="IMPORT">데이터 가져오기</option>
                                <option value="ERROR">오류</option>
                            </select>
                            <select name="result" class="form-select" style="width: 150px;">
                                <option value="">모든 결과</option>
                                <option value="SUCCESS">성공</option>
                                <option value="FAIL">실패</option>
                                <option value="ERROR">오류</option>
                            </select>
                            <input type="date" name="start_date" class="form-control" style="width: 180px;">
                            <input type="date" name="end_date" class="form-control" style="width: 180px;">
                            <div class="input-group flex-grow-1">
                                <input type="text" name="search" class="form-control" placeholder="내용 검색">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-search"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- 로그 테이블 -->
                <div class="table-responsive">
                    <table class="table table-hover datatable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>유형</th>
                                <th>메시지</th>
                                <th>IP</th>
                                <th>결과</th>
                                <th>사용자</th>
                                <th>생성일시</th>
                                <th>관리</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($logs)): ?>
                            <tr>
                                <td colspan="8" class="text-center">로그 데이터가 없습니다.</td>
                            </tr>
                            <?php else: ?>
                                <?php foreach ($logs as $log): ?>
                                <tr>
                                    <td><?php echo $log['id']; ?></td>
                                    <td>
                                        <span class="badge bg-<?php 
                                            echo $log['type'] === 'ERROR' ? 'danger' :
                                                ($log['type'] === 'API' ? 'info' :
                                                ($log['type'] === 'AUTH' ? 'warning' :
                                                ($log['type'] === 'SYSTEM' ? 'primary' : 'secondary')));
                                        ?>">
                                            <?php echo $log['type']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="log-message text-truncate" style="max-width: 300px;" title="<?php echo htmlspecialchars($log['message']); ?>">
                                            <?php echo htmlspecialchars($log['message']); ?>
                                        </div>
                                    </td>
                                    <td><?php echo $log['ip_address']; ?></td>
                                    <td>
                                        <span class="badge bg-<?php 
                                            echo $log['result'] === 'SUCCESS' ? 'success' :
                                                ($log['result'] === 'FAIL' ? 'warning' : 'danger');
                                        ?>">
                                            <?php echo $log['result']; ?>
                                        </span>
                                    </td>
                                    <td><?php echo $log['user_id'] ? $log['user_name'] : '시스템'; ?></td>
                                    <td><?php echo date('Y-m-d H:i:s', strtotime($log['created_at'])); ?></td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" class="btn btn-outline-primary btn-view" data-id="<?php echo $log['id']; ?>">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- 로그 상세 보기 모달 -->
    <div class="modal fade" id="logDetailModal" tabindex="-1" aria-labelledby="logDetailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="logDetailModalLabel">로그 상세 정보</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">로그 ID</label>
                                <p id="log_id"></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">유형</label>
                                <p id="log_type"></p>
                            </div>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">결과</label>
                                <p id="log_result"></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">IP 주소</label>
                                <p id="log_ip"></p>
                            </div>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">사용자</label>
                                <p id="log_user"></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">생성일시</label>
                                <p id="log_created_at"></p>
                            </div>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label class="form-label fw-bold">메시지</label>
                                <div id="log_message" class="p-3 bg-light rounded"></div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-0">
                                <label class="form-label fw-bold">추가 데이터</label>
                                <pre id="log_extra_data" class="p-3 bg-light rounded" style="max-height: 200px; overflow-y: auto;"></pre>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">닫기</button>
                </div>
            </div>
        </div>
    </div>
</main>

<style>
/* 로그 상세 테이블 스타일 */
.log-detail-table {
    table-layout: fixed;
    width: 100%;
}

.log-detail-table th {
    width: 150px;
    background-color: #f8f9fa;
    vertical-align: middle;
}

.log-detail-table td {
    word-wrap: break-word;
    overflow-wrap: break-word;
}

.word-break-all {
    word-break: break-all;
}

.code-block {
    background-color: #f8f9fa;
    border: 1px solid #eee;
    border-radius: 3px;
    max-height: 300px;
    overflow-y: auto;
    font-size: 0.9rem;
    padding: 10px;
    white-space: pre-wrap;
    word-break: break-all;
}
</style>

<script charset="utf-8" content="text/javascript;charset=utf-8">
// 글로벌 변수
let currentLogType = 'bo'; // 현재 활성화된 로그 유형 (bo, fo, api)
let boLogTable = null;
let foLogTable = null;
let apiLogTable = null;
let searchParams = {}; // 검색 조건을 전역변수로 유지

// 초기화
$(document).ready(function() {
    // 탭 변경 이벤트 리스너
    $('#logTabs a').on('click', function (e) {
        e.preventDefault();
        $(this).tab('show');
        currentLogType = $(this).data('log-type');
        loadCurrentLogData();
    });
    
    // 초기 데이터 로드
    initLogTables();
});

/**
 * Tabulator 공통 설정 함수
 * Tabulator 6.3 기준으로 최적화된 설정을 반환합니다.
 */
function getTabulatorCommonConfig(endpoint, columns) {
    return {
        height: "600px",
        layout: "fitColumns",
        placeholder: "로그 데이터가 없거나 로드 중입니다...",
        pagination: true,
        paginationMode: "remote",
        paginationSize: 10,
        paginationSizeSelector: [10, 20, 50, 100],
        filterMode: "remote",
        sortMode: "remote",
        ajaxURL: endpoint,
        ajaxConfig: "GET",
        ajaxContentType: "json",
        ajaxURLGenerator: function(url, config, params) {
            // 검색 파라미터와 Tabulator 파라미터 병합
            const queryParams = {...params, ...searchParams};
            
            // 페이지 관련 파라미터
            if (params.page) queryParams.page = params.page;
            if (params.size) queryParams.limit = params.size;
            
            // 필터 처리 (Tabulator 6.3 최적화)
            if (params.filter?.length) {
                const simplifiedFilters = params.filter.map(filter => ({
                    field: filter.field,
                    value: filter.value,
                    type: filter.type
                }));
                
                if (simplifiedFilters.length > 0) {
                    queryParams.filters = JSON.stringify(simplifiedFilters);
                }
                delete queryParams.filter;
            }
            
            // 정렬 처리
            if (params.sort?.length) {
                queryParams.sorters = JSON.stringify(params.sort.map(sort => ({
                    field: sort.field,
                    dir: sort.dir
                })));
            }
            
            return `${url}?${new URLSearchParams(queryParams).toString()}`;
        },
        ajaxResponse: function(url, params, response) {
            const logType = endpoint.split('/').pop(); // 'bo', 'fo', 'api'
            
            if (response.status === 'ok') {
                updateStatsInfo(logType, response.data.pagination);
                return {
                    data: response.data.list,
                    last_page: response.data.pagination.total_pages
                };
            }
            return { data: [], last_page: 0 };
        },
        columns: columns,
        rowHover: true,
        maxHeight: "calc(100vh - 400px)",
        renderVertical: "virtual", // 가상 스크롤링 활성화
        virtualDomBuffer: 100,     // 가상 DOM 버퍼 크기
        columnDefaults: {          // 모든 컬럼에 기본 설정 적용
            vertAlign: "middle",
            headerTooltip: true,
            resizable: true,
            headerFilter: true,
            headerFilterLiveFilter: true
        },
        dataLoaderLoading: "<div class='spinner-border text-primary' role='status'><span class='visually-hidden'>로딩중...</span></div>",
        dataLoaderError: "데이터를 불러오는 중 오류가 발생했습니다"
    };
}

// 로그 테이블 초기화
function initLogTables() {
    // 공통 필터 에디터 정의
    const resultFilterEditor = {
        headerFilter: "list",
        headerFilterParams: {
            values: {"SUCCESS": "성공", "FAIL": "실패", "ERROR": "에러"},
            clearable: true
        },
        headerFilterFunc: "="
    };
    
    const methodFilterEditor = {
        headerFilter: "list",
        headerFilterParams: {
            values: {"GET": "GET", "POST": "POST", "PUT": "PUT", "DELETE": "DELETE"},
            clearable: true
        },
        headerFilterFunc: "="
    };

    // BO 로그 테이블 컬럼 정의
    const boColumns = [
        { 
            title: "번호", 
            field: "bl_seq", 
            width: 100, 
            hozAlign: "left",
            sorter: "number",
            headerFilter: false
        },
        { 
            title: "몰 ID", 
            field: "mall_id", 
            width: 140,
            headerFilter: "input",
            headerFilterFunc: "="
        },
        { 
            title: "사용자 ID", 
            field: "user_id", 
            width: 140,
            headerFilter: "input",
            headerFilterFunc: "="
        },
        { 
            title: "타입", 
            field: "bl_type", 
            width: 150,
            headerFilter: "input",
            headerFilterFunc: "="
        },
        { 
            title: "액션", 
            field: "bl_action", 
            width: 130,
            headerFilter: "input",
            headerFilterFunc: "="
        },
        { 
            title: "결과", 
            field: "bl_result", 
            width: 100, 
            formatter: resultFormatter,
            ...resultFilterEditor
        },
        { 
            title: "상세내용", 
            field: "bl_detail",
            headerFilter: "input",
            headerFilterFunc: "like"
        },
        { 
            title: "IP", 
            field: "ip_addr", 
            width: 140,
            headerFilter: "input",
            headerFilterFunc: "starts"
        },
        { 
            title: "등록일시", 
            field: "reg_dt", 
            width: 160, 
            sorter: "datetime",
            formatter: formatDateTime,
            headerFilter: "input",
            headerFilterFunc: "like"
        },
        {
            title: "관리",
            width: 100,
            hozAlign: "center",
            formatter: cell => `<button class="btn btn-sm btn-info" title="상세보기" onclick="viewLogDetail('bo', ${cell.getRow().getData().bl_seq})"><i class="fas fa-eye"></i></button>`,
            headerSort: false,
            headerFilter: false,
            download: false
        }
    ];
    
    // FO 로그 테이블 컬럼 정의
    const foColumns = [
        { 
            title: "번호", 
            field: "fl_seq", 
            width: 100, 
            hozAlign: "left",
            sorter: "number",
            headerFilter: false
        },
        { 
            title: "몰 ID", 
            field: "mall_id", 
            width: 140,
            headerFilter: "input",
            headerFilterFunc: "="
        },
        { 
            title: "요청 URI", 
            field: "fl_request_uri",
            headerFilter: "input",
            headerFilterFunc: "like"
        },
        { 
            title: "결과", 
            field: "fl_result", 
            width: 100, 
            formatter: resultFormatter,
            ...resultFilterEditor
        },
        { 
            title: "IP", 
            field: "ip_addr", 
            width: 140,
            headerFilter: "input",
            headerFilterFunc: "starts"
        },
        { 
            title: "등록일시", 
            field: "reg_dt", 
            width: 160, 
            sorter: "datetime",
            formatter: formatDateTime,
            headerFilter: "input",
            headerFilterFunc: "like"
        },
        {
            title: "관리",
            width: 100,
            hozAlign: "center",
            formatter: cell => `<button class="btn btn-sm btn-info" title="상세보기" onclick="viewLogDetail('fo', ${cell.getRow().getData().fl_seq})"><i class="fas fa-eye"></i></button>`,
            headerSort: false,
            headerFilter: false,
            download: false
        }
    ];
    
    // API 로그 테이블 컬럼 정의
    const apiColumns = [
        { 
            title: "번호", 
            field: "al_seq", 
            width: 100, 
            hozAlign: "left",
            sorter: "number",
            headerFilter: false
        },
        { 
            title: "몰 ID", 
            field: "mall_id", 
            width: 140,
            headerFilter: "input",
            headerFilterFunc: "="
        },
        { 
            title: "사용자 ID", 
            field: "user_id", 
            width: 140,
            headerFilter: "input",
            headerFilterFunc: "="
        },
        { 
            title: "타입", 
            field: "al_type", 
            width: 150,
            headerFilter: "input",
            headerFilterFunc: "="
        },
        { 
            title: "엔드포인트", 
            field: "al_endpoint",
            headerFilter: "input",
            headerFilterFunc: "like"
        },
        { 
            title: "메소드", 
            field: "al_method", 
            width: 100,
            ...methodFilterEditor
        },
        { 
            title: "IP", 
            field: "ip_addr", 
            width: 140,
            headerFilter: "input",
            headerFilterFunc: "starts"
        },
        { 
            title: "등록일시", 
            field: "reg_dt", 
            width: 160, 
            sorter: "datetime",
            formatter: formatDateTime,
            headerFilter: "input",
            headerFilterFunc: "like"
        },
        {
            title: "관리",
            width: 100,
            hozAlign: "center",
            formatter: cell => `<button class="btn btn-sm btn-info" title="상세보기" onclick="viewLogDetail('api', ${cell.getRow().getData().al_seq})"><i class="fas fa-eye"></i></button>`,
            headerSort: false,
            headerFilter: false,
            download: false
        }
    ];
    
    // 테이블 초기화 및 공통 설정 적용
    const printDateTime = formatDateTime(new Date().toISOString());
    
    // 테이블 인스턴스 생성
    boLogTable = new Tabulator("#boLogTable", {
        ...getTabulatorCommonConfig("api/logs/bo", boColumns),
        printAsHtml: true,
        printHeader: "<h2>관리자 로그</h2>",
        printFooter: `<p>출력일시: ${printDateTime}</p>`
    });
    
    foLogTable = new Tabulator("#foLogTable", {
        ...getTabulatorCommonConfig("api/logs/fo", foColumns),
        printAsHtml: true,
        printHeader: "<h2>프론트 로그</h2>", 
        printFooter: `<p>출력일시: ${printDateTime}</p>`
    });
    
    apiLogTable = new Tabulator("#apiLogTable", {
        ...getTabulatorCommonConfig("api/logs/api", apiColumns),
        printAsHtml: true,
        printHeader: "<h2>API 로그</h2>",
        printFooter: `<p>출력일시: ${printDateTime}</p>`
    });
    
    // Tabulator 6.3 최적화: 테이블 배열로 관리하여 공통 이벤트 처리
    const tables = {
        bo: boLogTable,
        fo: foLogTable,
        api: apiLogTable
    };
    
    // 테이블 생성 완료 이벤트 처리
    Object.entries(tables).forEach(([type, table]) => {
        table.on("tableBuilt", function() {
            if (currentLogType === type) {
                loadLogData(type);
            }
        });
        
        // 데이터 로딩 이벤트 처리
        table.on("dataLoading", () => $('#loading').show());
        table.on("dataLoaded", () => $('#loading').hide());
        table.on("dataLoadError", (error) => {
            console.error("데이터 로드 오류:", error);
            $('#loading').hide();
            $.alert('데이터 로드 중 오류가 발생했습니다', null, '실패', 'error');
        });
        
        // 필터 변경 시 디바운스 처리
        table.on("headerFilterChanged", function() {
            clearTimeout(table.filterTimeout);
            table.filterTimeout = setTimeout(() => table.setData(), 600);
        });
    });
    
    // 기간 검색 추가
    addDateRangeSearchToTabs();
}

/**
 * 각 탭에 기간 검색 인터페이스 추가
 */
function addDateRangeSearchToTabs() {
    const dateRangeHTML = `
    <div class="d-flex align-items-center mb-3 date-range-filter">
        <div class="input-group me-2" style="width: auto;">
            <span class="input-group-text"><i class="fas fa-calendar"></i></span>
            <input type="date" class="form-control start-date" placeholder="시작일">
        </div>
        <span class="mx-1">~</span>
        <div class="input-group me-3" style="width: auto;">
            <input type="date" class="form-control end-date" placeholder="종료일">
        </div>
        <button class="btn btn-outline-primary btn-apply-date" type="button">기간 적용</button>
        <button class="btn btn-outline-secondary btn-reset-filters ms-2" type="button">초기화</button>
    </div>`;
    
    // 각 탭에 날짜 검색 필터 추가
    $('#boLog .d-flex.justify-content-end').before(dateRangeHTML);
    $('#foLog .d-flex.justify-content-end').before(dateRangeHTML);
    $('#apiLog .d-flex.justify-content-end').before(dateRangeHTML);
    
    // 초기 날짜 설정
    const today = new Date();
    const oneMonthAgo = new Date();
    oneMonthAgo.setMonth(today.getMonth() - 1);
    
    // 날짜 포맷 변환 (YYYY-MM-DD)
    const formatDateForInput = (date) => {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    };
    
    // 날짜 초기화
    $('.start-date').val(formatDateForInput(oneMonthAgo));
    $('.end-date').val(formatDateForInput(today));
    
    // 테이블 매핑
    const tables = {
        boLog: boLogTable,
        foLog: foLogTable,
        apiLog: apiLogTable
    };
    
    // 기간 적용 버튼 이벤트
    $('.btn-apply-date').on('click', function() {
        const tabPane = $(this).closest('.tab-pane');
        const startDate = $(this).closest('.date-range-filter').find('.start-date').val();
        const endDate = $(this).closest('.date-range-filter').find('.end-date').val();
        
        if (startDate && endDate) {
            const table = tables[tabPane.attr('id')];
            if (table) {
                searchParams.start_date = startDate;
                searchParams.end_date = endDate;
                table.setData();
            }
        }
    });
    
    // 필터 초기화 버튼 이벤트
    $('.btn-reset-filters').on('click', function() {
        const tabPane = $(this).closest('.tab-pane');
        const table = tables[tabPane.attr('id')];
        
        if (table) {
            // 날짜 초기화
            $(this).closest('.date-range-filter').find('.start-date').val(formatDateForInput(oneMonthAgo));
            $(this).closest('.date-range-filter').find('.end-date').val(formatDateForInput(today));
            
            // 필터 초기화
            table.clearHeaderFilter();
            searchParams = {};
            table.setData();
        }
    });
}

// 결과 포맷터
function resultFormatter(cell) {
    const value = cell.getValue();
    let className = '';
    
    if (value === 'SUCCESS') {
        className = 'bg-success text-white';
    } else if (value === 'FAIL') {
        className = 'bg-warning text-dark';
    } else if (value === 'ERROR') {
        className = 'bg-danger text-white';
    }
    
    return className ? `<span class="badge ${className}">${value}</span>` : value;
}

// 현재 선택된 로그 유형 데이터 로드
function loadCurrentLogData() {
    loadLogData(currentLogType);
}

// 특정 로그 유형 데이터 로드
function loadLogData(logType) {
    // 필요한 날짜 파라미터만 유지
    const startDate = searchParams.start_date;
    const endDate = searchParams.end_date;
    searchParams = {};
    
    if (startDate) searchParams.start_date = startDate;
    if (endDate) searchParams.end_date = endDate;
    
    const tables = {
        bo: boLogTable,
        fo: foLogTable,
        api: apiLogTable
    };
    
    if (tables[logType]) {
        tables[logType].clearData();
        tables[logType].setData();
    }
}

// 통계 정보 업데이트
function updateStatsInfo(logType, pagination) {
    const totalCount = pagination.total_count;
    const currentPage = pagination.page;
    const pageSize = pagination.limit;
    
    const startItem = (currentPage - 1) * pageSize + 1;
    const endItem = Math.min(currentPage * pageSize, totalCount);
    
    $(`#${logType}LogStats`).text(`총 ${totalCount.toLocaleString()}개 로그 중 ${startItem.toLocaleString()}-${endItem.toLocaleString()}`);
}

// 내보내기 함수
function exportCurrentLogToCsv() {
    const table = getActiveTable();
    if (table) {
        table.download("csv", `${currentLogType}_로그_데이터.csv`, {
            delimiter: ",",
            bom: true,
            columnHeaders: true
        });
    }
}

function exportCurrentLogToExcel() {
    const table = getActiveTable();
    const sheetNames = {
        bo: "관리자 로그",
        fo: "프론트 로그",
        api: "API 로그"
    };
    
    if (table) {
        table.download("xlsx", `${currentLogType}_로그_데이터.xlsx`, {
            sheetName: sheetNames[currentLogType],
            documentTitle: "로그 데이터 내보내기",
            orientation: "landscape"
        });
    }
}

// 현재 활성화된 테이블 반환
function getActiveTable() {
    const tables = {
        bo: boLogTable,
        fo: foLogTable,
        api: apiLogTable
    };
    return tables[currentLogType];
}

// 로그 상세 정보 보기
function viewLogDetail(logType, logId) {
    $('#loading').show();
    
    $.ajax({
        url: `api/logs/${logType}/${logId}`,
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.status === 'ok' && response.data) {
                const logItem = response.data;
                
                // 모달 제목 설정
                const modalTitles = {
                    bo: '관리자 로그 상세 정보',
                    fo: '프론트 로그 상세 정보',
                    api: 'API 로그 상세 정보'
                };
                $('#logDetailTitle').text(modalTitles[logType]);
                
                // 모달 내용 생성
                let detailHtml = '<table class="table table-bordered log-detail-table">';
                
                if (logType === 'bo') {
                    detailHtml += `
                        <tr><th width="150">로그 번호</th><td>${logItem.bl_seq}</td></tr>
                        <tr><th>몰 ID</th><td>${logItem.mall_id}</td></tr>
                        <tr><th>사용자 ID</th><td>${logItem.user_id}</td></tr>
                        <tr><th>요청 URI</th><td class="text-break word-break-all">${logItem.bl_request_uri || '-'}</td></tr>
                        <tr><th>로그 타입</th><td>${logItem.bl_type}</td></tr>
                        <tr><th>로그 액션</th><td>${logItem.bl_action || '-'}</td></tr>
                        <tr><th>결과</th><td>${resultFormatter({getValue: () => logItem.bl_result})}</td></tr>
                        <tr><th>로그 상세내용</th><td class="text-break word-break-all">${logItem.bl_detail || '-'}</td></tr>
                        <tr><th>IP 주소</th><td>${logItem.ip_addr || '-'}</td></tr>
                        <tr><th>등록일시</th><td>${logItem.reg_dt}</td></tr>
                    `;
                    
                    // 파라미터가 있으면 파싱
                    if (logItem.bl_params) {
                        try {
                            const params = JSON.parse(logItem.bl_params);
                            detailHtml += `<tr><th>파라미터</th><td><pre class="mt-2 code-block">${JSON.stringify(params, null, 2)}</pre></td></tr>`;
                        } catch (e) {
                            detailHtml += `<tr><th>파라미터</th><td class="text-break word-break-all">${logItem.bl_params}</td></tr>`;
                        }
                    }
                } else if (logType === 'fo') {
                    detailHtml += `
                        <tr><th width="150">로그 번호</th><td>${logItem.fl_seq}</td></tr>
                        <tr><th>몰 ID</th><td>${logItem.mall_id}</td></tr>
                        <tr><th>요청 URI</th><td class="text-break word-break-all">${logItem.fl_request_uri}</td></tr>
                        <tr><th>결과</th><td>${resultFormatter({getValue: () => logItem.fl_result})}</td></tr>
                        <tr><th>IP 주소</th><td>${logItem.ip_addr || '-'}</td></tr>
                        <tr><th>등록일시</th><td>${logItem.reg_dt}</td></tr>
                    `;
                    
                    // JSON 데이터 처리 함수
                    const formatJsonData = (data) => {
                        try {
                            return `<pre class="mt-2 code-block">${JSON.stringify(JSON.parse(data), null, 2)}</pre>`;
                        } catch (e) {
                            return `<div class="text-break word-break-all">${data}</div>`;
                        }
                    };
                    
                    // 요청/응답 데이터
                    if (logItem.fl_request_data) {
                        detailHtml += `<tr><th>요청 데이터</th><td>${formatJsonData(logItem.fl_request_data)}</td></tr>`;
                    }
                    
                    if (logItem.fl_response_data) {
                        detailHtml += `<tr><th>응답 데이터</th><td>${formatJsonData(logItem.fl_response_data)}</td></tr>`;
                    }
                } else if (logType === 'api') {
                    detailHtml += `
                        <tr><th width="150">로그 번호</th><td>${logItem.al_seq}</td></tr>
                        <tr><th>몰 ID</th><td>${logItem.mall_id}</td></tr>
                        <tr><th>사용자 ID</th><td>${logItem.user_id}</td></tr>
                        <tr><th>API 타입</th><td>${logItem.al_type}</td></tr>
                        <tr><th>요청 URI</th><td class="text-break word-break-all">${logItem.al_request_uri || '-'}</td></tr>
                        <tr><th>API 엔드포인트</th><td class="text-break word-break-all">${logItem.al_endpoint}</td></tr>
                        <tr><th>메소드</th><td><span class="badge bg-primary">${logItem.al_method}</span></td></tr>
                        <tr><th>IP 주소</th><td>${logItem.ip_addr || '-'}</td></tr>
                        <tr><th>등록일시</th><td>${logItem.reg_dt}</td></tr>
                    `;
                    
                    // JSON 데이터 처리 함수
                    const formatJsonData = (data) => {
                        try {
                            return `<pre class="mt-2 code-block">${JSON.stringify(JSON.parse(data), null, 2)}</pre>`;
                        } catch (e) {
                            return `<div class="text-break word-break-all">${data}</div>`;
                        }
                    };
                    
                    // API 데이터
                    if (logItem.al_data) {
                        detailHtml += `<tr><th>API 전송 데이터</th><td>${formatJsonData(logItem.al_data)}</td></tr>`;
                    }
                    
                    if (logItem.al_response) {
                        detailHtml += `<tr><th>API 호출 응답</th><td>${formatJsonData(logItem.al_response)}</td></tr>`;
                    }
                    
                    if (logItem.al_error_msg) {
                        detailHtml += `<tr><th>API 에러메시지</th><td class="text-danger text-break word-break-all">${logItem.al_error_msg}</td></tr>`;
                    }
                }
                
                detailHtml += '</table>';
                
                // 모달에 내용 삽입 및 표시
                $('#logDetailContent').html(detailHtml);
                openModal('modal_logDetail', 'modalBackdrop_logDetail');
            } else {
                $.alert('데이터를 불러올 수 없습니다.', null, '실패', 'error');
            }
        },
        error: function(e) {
            const errorMsg = e.responseJSON?.message || '데이터를 불러오는 중 에러가 발생했습니다.';
            $.alert(errorMsg, null, '실패', 'error');
        },
        complete: function() {
            $('#loading').hide();
        }
    });
}

// 로그 상세 모달 닫기
function closeLogDetailModal() {
    closeModal('modal_logDetail', 'modalBackdrop_logDetail');
}

// 날짜 포맷 도우미 함수
function formatDateTime(cellVal) {
    const dateStr = typeof cellVal === 'object' ? cellVal.getValue() : cellVal;
    if (!dateStr) return '-';
    
    const date = new Date(dateStr);
    
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    const hours = String(date.getHours()).padStart(2, '0');
    const minutes = String(date.getMinutes()).padStart(2, '0');
    const seconds = String(date.getSeconds()).padStart(2, '0');
    
    return `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;
}
</script>