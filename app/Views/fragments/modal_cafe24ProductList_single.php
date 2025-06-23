<?php
// modal_cafe24ProductList.php
?>
<style>
    .tabulator .tabulator-tableholder { max-height: 400px !important; }
    .tabulator .tabulator-tableholder .tabulator-table { height: 100%; }
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
            selectable: 1,
            index: "product_no",
            placeholder: "데이터가 존재하지 않습니다.",

            // 서버 페이징 설정
            pagination: true,
            paginationMode: "remote",
            paginationSize: 10,
            paginationSizeSelector: [10, 50, 100],
            // paginationCounter: "rows",

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
    function selectCategory(obj, depth) {
        $('#loading').show();

        const category = obj ? obj.value : null;

        $.ajax({
            url: 'api/cafe24/categories',
            type: 'GET',
            data: { category, depth },
            dataType: 'json',
            success: function(response) {
                // 응답 데이터 검증
                if (!response || !response.data || !Array.isArray(response.data.list)) {
                    console.error('예상치 못한 응답 형식:', response);
                    $.alert('데이터 형식이 올바르지 않습니다.', null, '실패', 'error');
                    return;
                }

                // 각 분류별 기본 텍스트 매핑
                const placeholderMapping = {
                    0: "대분류 선택",
                    1: "중분류 선택",
                    2: "소분류 선택",
                    3: "상세분류 선택"
                };

                // 현재 업데이트할 select box의 기본 텍스트 결정
                const currentPlaceholder = obj ? placeholderMapping[depth] : placeholderMapping[0];

                // 옵션 HTML 생성 (기본 "선택" 옵션 포함)
                const optionsHtml = `<option value="">${currentPlaceholder}</option>` +
                    response.data.list.map(item => `<option value="${item.category_no}">${item.category_name}</option>`).join('');

                // 선택된 엘리먼트와 depth에 따른 각 select box 업데이트
                if (!obj) {
                    $("#big_category").html(optionsHtml);
                    $("#middle_category").html(`<option value=''>중분류 선택</option>`);
                    $("#small_category").html(`<option value=''>소분류 선택</option>`);
                    $("#detail_category").html(`<option value=''>상세분류 선택</option>`);
                } else if (depth === 1) {
                    $("#middle_category").html(optionsHtml);
                    $("#small_category").html(`<option value=''>소분류 선택</option>`);
                    $("#detail_category").html(`<option value=''>상세분류 선택</option>`);
                } else if (depth === 2) {
                    $("#small_category").html(optionsHtml);
                } else if (depth === 3) {
                    $("#detail_category").html(optionsHtml);
                }
            },
            error: function(jqXHR) {
                const errorMsg = (jqXHR.responseJSON && jqXHR.responseJSON.error) ? 
                    jqXHR.responseJSON.error : '데이터를 가져오는 중 에러가 발생했습니다.';
                console.error('AJAX 에러:', jqXHR);
                $.alert(errorMsg, null, '실패', 'error');
            },
            complete: function() {
                $('#loading').hide();
            }
        });
    }

    /**
     * 선택 완료 버튼 → 선택된 상품 목록을 다른 테이블/리스트에 반영
     */
    function applySelectedProducts() {
        const $selectedTableBody = $("#selectedProductsTable tbody");
        const selectedData = cafe24Table.getSelectedData();

        // 선택된 상품이 없는 경우
        if (!selectedData || selectedData.length === 0) {
            $.alert({
                title: '알림',
                content: '상품을 선택해주세요.',
                type: 'orange'
            });
            return;
        }

        // 기존 선택된 상품 제거
        $selectedTableBody.find(".selected-product").remove();
        $('#selectedProductsContainer').hide();

        // 새로운 상품 추가
        const { product_no, product_name, product_code, detail_image, price } = selectedData[0];

        const $newRow = $(`
            <tr class="selected-product" data-prodno="${product_no}" data-productcode="${product_code || ''}">
                <td>${product_no}</td>
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
        $('#selectedProductsContainer').show();

        // 모달창 닫기
        closeModal('modal_cafe24ProductList', 'modalBackdrop_cafe24ProductList');
    }

    // 선택된 상품 삭제 이벤트 핸들러 추가
    $(document).on('click', '.js-remove-selected-product', function() {
        $(this).closest('tr').remove();
        if ($("#selectedProductsTable tbody tr").length === 0) {
            $('#selectedProductsContainer').hide();
        }
    });

    // 문서가 준비되면 테이블 초기화
    $(document).ready(function() {
        initCafe24Table();
        selectCategory(null, 0);
    });
</script>
