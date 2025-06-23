<?php
// modal_cafe24ProductList.php
?>
<style>
    .tabulator .tabulator-tableholder { max-height: 400px !important; }
    .tabulator .tabulator-tableholder .tabulator-table { height: 100%; }
    .tabulator-row.tabulator-selected {
        /* background-color: #fdffdb !important; */
      }
      .tabulator-row .tabulator-cell[tabulaor-field="detail_image"] {
        text-overflow: unset;
        white-space: normal;
      }
</style>
<!-- 모달 백드롭 -->
<div id="modalBackdrop_cafe24ProductList" class="modal-backdrop"></div>

<!-- 상품 검색/선택 모달 -->
<div id="modal_cafe24ProductList" class="modal" role="dialog" aria-modal="true">
    <div class="modal-content" style="max-width:1400px;">

        <div class="modal-header d-flex align-items-center">
            <h2 class="h5 mb-0">상품 검색</h2>
            <button type="button" class="btn-close" onclick="closeModal('modal_cafe24ProductList', 'modalBackdrop_cafe24ProductList')"></button>
        </div>

        <div class="modal-body">
            <!-- 검색 영역 -->
            <div class="card mb-4 bg-light">
                <div class="card-body">
                    <!-- 상품 검색 (상품명 / 상품코드) -->
                    <div class="row g-3 align-items-center mb-3">
                        <div class="col-md-2 fw-bold">상품 검색</div>
                        <div class="col-md-10 d-flex align-items-center flex-wrap">
                            <!-- 라디오 버튼: 상품명 / 상품코드 -->
                            <div class="form-check me-3">
                                <input class="form-check-input" type="radio" name="searchType" id="searchByName" value="product_name" checked />
                                <label class="form-check-label" for="searchByName">상품명</label>
                            </div>
                            <div class="form-check me-3">
                                <input class="form-check-input" type="radio" name="searchType" id="searchByCode" value="product_code" />
                                <label class="form-check-label" for="searchByCode">상품코드</label>
                            </div>
                            <!-- 검색어 입력 -->
                            <div class="d-flex align-items-center flex-wrap">
                                <input
                                    type="text"
                                    id="searchKeyword"
                                    class="form-control w-auto me-2"
                                    placeholder="검색어 입력"
                                    onkeyup="if(event.key==='Enter') searchCafe24Products();"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- 카테고리 검색 -->
                    <div class="row g-3 align-items-center mb-3">
                        <div class="col-md-2 fw-bold">상품 분류</div>
                        <div class="col-md-10 d-flex flex-wrap align-items-center">
                            <select id="big_category" class="form-select w-auto me-2" onchange="selectCategory(this, 1)">
                                <option value="">대분류 선택</option>
                            </select>
                            <select id="middle_category" class="form-select w-auto me-2" onchange="selectCategory(this, 2)">
                                <option value="">중분류 선택</option>
                            </select>
                            <select id="small_category" class="form-select w-auto me-2" onchange="selectCategory(this, 3)">
                                <option value="">소분류 선택</option>
                            </select>
                            <select id="detail_category" class="form-select w-auto me-2" onchange="selectCategory(this, 4)">
                                <option value="">상세분류 선택</option>
                            </select>
                            <p class="text-muted mb-0 mt-2">※ 카테고리에 따라 검색 시간이 다소 소요될 수 있습니다.</p>
                        </div>
                    </div>

                    <!-- 진열 상태 / 판매 상태 -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-2 fw-bold">진열 상태</div>
                        <div class="col-md-4">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="displayStatus" id="displayAll" value="" checked />
                                <label class="form-check-label" for="displayAll">전체</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="displayStatus" id="displayYes" value="T" />
                                <label class="form-check-label" for="displayYes">진열함</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="displayStatus" id="displayNo" value="F" />
                                <label class="form-check-label" for="displayNo">진열안함</label>
                            </div>
                        </div>

                        <div class="col-md-2 fw-bold">판매 상태</div>
                        <div class="col-md-4">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="salesStatus" id="salesAll" value="" checked />
                                <label class="form-check-label" for="salesAll">전체</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="salesStatus" id="salesYes" value="T" />
                                <label class="form-check-label" for="salesYes">판매함</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="salesStatus" id="salesNo" value="F" />
                                <label class="form-check-label" for="salesNo">판매안함</label>
                            </div>
                        </div>
                    </div>

                    <!-- 검색 버튼 / 전체선택 / 전체 해제 -->
                    <div class="text-center">
                        <button type="button" class="btn btn-secondary mb-2" onclick="searchCafe24Products()" style="width:100%;">
                            검색
                        </button>
                        <div class="d-flex justify-content-center gap-2">
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="selectAllCafe24Rows()">전체 선택</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="deselectAllCafe24Rows()">전체 해제</button>
                        </div>
                    </div>
                </div>
            </div><!-- //card (검색 영역) -->

            <!-- 검색 결과 (Tabulator) -->
            <div class="card mb-3">
                <div class="card-body p-0">
                    <div id="cafe24ProductTable" style="border: 1px solid #ccc; min-height:300px;">
                        <!-- Tabulator 표가 로드될 영역 -->
                    </div>
                </div>
            </div><!-- //card (검색결과) -->
        </div><!-- //.modal-body -->

        <div class="modal-footer">
            <button type="button" class="btn btn-primary me-2" onclick="applySelectedProducts()">
                선택완료
            </button>
            <button type="button" class="btn btn-secondary" onclick="closeModal('modal_cafe24ProductList', 'modalBackdrop_cafe24ProductList')">
                닫기
            </button>
        </div>
    </div><!-- //.modal-content -->
</div><!-- //#modal_cafe24ProductList -->

<script charset="utf-8" content="text/javascript;charset=utf-8">
    let cafe24Table = null;
    let searchParams = {}; // 검색 조건을 전역변수로 유지

    // key: product_no, value: 상품정보(Object)
    let selectedProductsMap = new Map(); 

    /**
     * 페이지네이션 버튼(li>a) 생성 헬퍼
     * @param {string} text 버튼에 표시될 내용
     * @param {function} onClick 클릭 시 호출할 함수
     * @param {boolean} isActive 현재 페이지인지 여부
     */
    function createPageItem(text, onClick, isActive = false) {
        const li = document.createElement("li");
        li.className = "page-item" + (isActive ? " active" : "");

        const a = document.createElement("a");
        a.className = "page-link";
        a.href = "#";
        a.innerHTML = text;
        a.onclick = e => {
            e.preventDefault();
            onClick();
        };

        li.appendChild(a);
        return li;
    }

    /**
     * 커스텀 페이지네이션 갱신
     * - Tabulator의 현재 페이지/총 페이지를 구해서 <ul> 요소를 갱신
     */
    function updateCafe24Pagination() {
        const currentPage = cafe24Table.getPage() || 1;
        const totalPages  = cafe24Table.getPageMax() || 1;

        const paginationEl = document.getElementById("cafe24Pagination");
        if (!paginationEl) return;

        paginationEl.innerHTML = "";

        // 이전 페이지로
        paginationEl.appendChild(
            createPageItem("&laquo;", () => cafe24Table.previousPage())
        );

        // 페이지 번호들 생성
        for (let i = 1; i <= totalPages; i++) {
            paginationEl.appendChild(
                createPageItem(i, () => cafe24Table.setPage(i), i === currentPage)
            );
        }

        // 다음 페이지로
        paginationEl.appendChild(
            createPageItem("&raquo;", () => cafe24Table.nextPage())
        );
    }

    /**
     * 테이블 초기화
     */
    function initCafe24Table() {
        cafe24Table = new Tabulator("#cafe24ProductTable", {
            layout: "fitColumns",
            resizableColumnFit: true,
            selectable: true,
            index: "product_no",
            placeholder: "데이터가 존재하지 않습니다.",

            // 서버 페이징 설정
            pagination: true,
            paginationMode: "remote",
            paginationSize: 10,
            paginationSizeSelector: [10, 50, 100],
            paginationCounter: "rows",

            // AJAX 설정
            ajaxURL: "api/cafe24/products",
            ajaxConfig: "GET",
            ajaxContentType: "json",
            ajaxURLGenerator: function(url, config, params) {
                const queryString = new URLSearchParams({...params, ...searchParams}).toString();
                if (params.page) delete searchParams.page;
                if (params.size) delete searchParams.size;
                return `${url}?${queryString}`;
            },

            ajaxResponse: function(url, params, response) {
                if (response.status !== 'ok') {
                    $.alert("데이터를 불러오는 중 오류가 발생했습니다.");
                    return { last_page: 1, data: [] };
                }

                const resData = response.data.result;
                return {
                    last_page: resData.totalPages || 1,
                    data: resData.products || []
                };
            },

            ajaxError: function(error) {
                console.error("AJAX Error:", error);
                $.alert("상품 데이터를 불러오는 데 실패했습니다.");
            },

            // 테이블 컬럼 정의
            columns: [
                { title: "상품번호", field: "product_no", width: 120, hozAlign: "center" },
                { title: "상품코드", field: "product_code", width: 120, hozAlign: "center" },
                {
                    title: "이미지",
                    field: "detail_image",
                    width: 120,
                    hozAlign: "center",
                    formatter: "image",
                    formatterParams: { height: "80px" }
                },
                { title: "상품명", field: "product_name" },
                { 
                    title: "가격", 
                    field: "price", 
                    width: 150, 
                    hozAlign: "right",
                    formatter: "money",
                    formatterParams: {
                        symbol: "원",
                        symbolAfter: true,
                        precision: 0
                    }
                },
                { title: "진열상태", field: "display", width: 120, hozAlign: "center" },
                { title: "판매상태", field: "selling", width: 120, hozAlign: "center" },
            ],
        });
    }

    /**
     * 검색 버튼 클릭 시
     * - 검색/카테고리/진열상태/판매상태 등 여러 파라미터를 합쳐 요청
     * - cafe24Table.setData()로 새로운 요청
     */
    function searchCafe24Products() {
        const searchType     = $('input[name="searchType"]:checked').val();
        const searchKeyword  = $("#searchKeyword").val();
        const bigCategory    = $("#big_category").val();
        const middleCategory = $("#middle_category").val();
        const smallCategory  = $("#small_category").val();
        const detailCategory = $("#detail_category").val();
        const displayStatus  = $('input[name="displayStatus"]:checked').val();
        const salesStatus    = $('input[name="salesStatus"]:checked').val();

        searchParams = {
            searchType,
            searchKeyword,
            bigCategory,
            middleCategory,
            smallCategory,
            detailCategory,
            displayStatus,
            salesStatus
        };

        // Tabulator 서버쪽 Ajax 재호출
        cafe24Table.setData("api/cafe24/products", searchParams);
    }

    /**
     * 카테고리 선택 예시
     */
    function selectCategory(el, level) {
        console.log("카테고리 선택(Level " + level + "): " + el.value);
        // 필요 시, 여기에서 하위 카테고리 갱신 로직 추가
    }

    /**
     * 선택 완료 버튼 → 선택된 상품 목록을 다른 테이블/리스트에 반영
     */
    function applySelectedProducts() {
        const $selectedTableBody = $("#selectedProductsTable tbody");

        // (1) 기존 목록에서 이미 선택 해제된 항목 제거
        $selectedTableBody.find("tr").each(function() {
            const rowProdNo = String($(this).data("prodno"));
            // 전역 Map에 없으면 해제된 것으로 간주하고 삭제
            if (!selectedProductsMap.has(rowProdNo)) {
                $(this).remove();
            }
        });

        // (2) 이미 추가된 상품번호 목록
        const alreadySelected = $selectedTableBody.find("tr").map(function() {
            return String($(this).data("prodno"));
        }).get();

        // (3) Map에 저장된 상품들 중, 아직 미반영된 것들을 새로 추가
        selectedProductsMap.forEach((productData, prodNo) => {
            // 만약 테이블에 이미 추가돼 있다면 생략
            if (alreadySelected.includes(prodNo)) return;
            const { product_name, product_code, detail_image, price } = productData;
            // 필요하다면 다른 필드들도 추가

            const $newRow = $(`
                <tr data-prodno="${prodNo}">
                    <td>${prodNo}</td>
                    <td>${product_code}</td>
                    <td>
                        <img src="${detail_image}" alt="${product_name}" style="height:40px;">
                    </td>
                    <td>${product_name}</td>
                    <td>${number_format(price)}원</td>
                    <td>
                        <button type="button" class="btn btn-danger btn-sm js-remove-selected-product">
                            삭제
                        </button>
                    </td>
                </tr>
            `);

            $selectedTableBody.append($newRow);
        });

        // 모달창 닫기
        closeModal('modal_cafe24ProductList', 'modalBackdrop_cafe24ProductList');
    }

    // 전체 선택 / 전체 해제
    function selectAllCafe24Rows() {
        cafe24Table.selectRow();
    }
    function deselectAllCafe24Rows() {
        cafe24Table.deselectRow();
    }

    // 문서가 준비되면 테이블 초기화
    $(document).ready(function() {
        initCafe24Table();

        // row 선택 시 상품정보를 Map에 추가
        cafe24Table.on("rowSelected", function(row) {
            const data = row.getData();
            // 원하는 데이터(상품번호, 상품코드, 상품명, 이미지, 가격 등)를 Map에 저장
            selectedProductsMap.set(data.product_no, {
                product_no:    data.product_no,
                product_code:  data.product_code,
                product_name:  data.product_name,
                detail_image:  data.detail_image,
                price:         data.price,
                // etc...
            });
        });

        // row 해제 시 Map에서 해당 상품정보 제거
        cafe24Table.on("rowDeselected", function(row) {
            const data = row.getData();
            selectedProductsMap.delete(data.product_no);
        });

        // 페이지 이동 시, 이미 선택했던 상품들은 다시 체크
        cafe24Table.on("dataProcessed", function(data) {
            data.forEach(rowData => {
                if (selectedProductsMap.has(rowData.product_no)) {
                    cafe24Table.selectRow(rowData.product_no);
                }
            });
        });
    });
</script>
