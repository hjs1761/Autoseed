<?php
/**
 * 파일: app/Controllers/InfluencerController.php
 * 
 * 이 파일은 인플루언서 웹 인터페이스를 위한 컨트롤러 클래스를 정의합니다.
 * 사용자의 요청을 처리하고, 서비스 계층을 통해 필요한 데이터를 가져와 뷰에 전달하는 역할을 합니다.
 * 인플루언서 목록 조회, 상세 정보 조회, 생성, 수정, 삭제 등의 기능을 제공합니다.
 * 
 * @package App\Controllers
 */

namespace App\Controllers;

use App\Core\BaseController;
use App\Core\Http\Request;
use App\Services\InfluencerService;
use App\Models\Platform;
use App\Models\Category;

/**
 * 인플루언서 컨트롤러 클래스
 * 
 * 인플루언서 웹 인터페이스를 위한 컨트롤러입니다.
 */
class InfluencerController extends BaseController
{
    private $influencerService;
    
    /**
     * 컨트롤러 인스턴스를 초기화합니다.
     */
    public function __construct()
    {
        parent::__construct();
        $this->influencerService = new InfluencerService();
    }
    
    /**
     * 인플루언서 목록 페이지를 표시합니다.
     * 
     * @param Request $request 요청 객체
     * @return mixed 렌더링된 뷰
     */
    public function index(Request $request)
    {
        $page = $request->getQuery('page', 1);
        $limit = 20;
        $keyword = $request->getQuery('keyword', '');
        $platformId = $request->getQuery('platform_id');
        $categoryId = $request->getQuery('category_id');
        
        $params = [
            'keyword' => $keyword,
            'platform_id' => $platformId,
            'category_id' => $categoryId,
            'page' => $page,
            'limit' => $limit
        ];
        
        $result = $this->influencerService->search($params);
        
        if (!$result['success']) {
            $this->setFlash('error', $result['message']);
            $influencers = [];
            $pagination = [
                'total' => 0,
                'per_page' => $limit,
                'current_page' => $page,
                'last_page' => 1,
                'from' => 0,
                'to' => 0
            ];
        } else {
            $influencers = $result['data']['influencers'];
            $pagination = $result['data']['pagination'];
        }
        
        $platforms = Platform::all();
        $categories = Category::all();
        
        return $this->render('influencer/list', [
            'influencers' => $influencers,
            'platforms' => $platforms,
            'categories' => $categories,
            'keyword' => $keyword,
            'platformId' => $platformId,
            'categoryId' => $categoryId,
            'page' => $page,
            'pagination' => $pagination
        ]);
    }
    
    /**
     * 인플루언서 상세 페이지를 표시합니다.
     * 
     * @param Request $request 요청 객체
     * @param int $id 인플루언서 ID
     * @return mixed 렌더링된 뷰 또는 리디렉션
     */
    public function show(Request $request, $id)
    {
        $result = $this->influencerService->getInfluencerDetail($id);
        
        if (!$result['success']) {
            $this->setFlash('error', $result['message']);
            $this->redirect('/influencers');
            return;
        }
        
        return $this->render('influencer/detail', [
            'influencer' => $result['data']['influencer'],
            'categories' => $result['data']['categories'],
            'platform' => $result['data']['platform']
        ]);
    }
    
    /**
     * 인플루언서 검색 페이지를 표시합니다.
     * 
     * @param Request $request 요청 객체
     * @return mixed 렌더링된 뷰
     */
    public function search(Request $request)
    {
        $platforms = Platform::all();
        $categories = Category::all();
        
        return $this->render('influencer/search', [
            'platforms' => $platforms,
            'categories' => $categories
        ]);
    }
    
    /**
     * 인플루언서 검색 결과를 처리하고 리디렉션합니다.
     * 
     * @param Request $request 요청 객체
     * @return void
     */
    public function doSearch(Request $request)
    {
        $keyword = $request->getPost('keyword', '');
        $platformId = $request->getPost('platform_id');
        $categoryId = $request->getPost('category_id');
        
        $this->redirect("/influencers?keyword=$keyword&platform_id=$platformId&category_id=$categoryId");
    }
    
    /**
     * 인플루언서 생성/편집 폼을 표시합니다.
     * 
     * @param Request $request 요청 객체
     * @param int|null $id 인플루언서 ID (편집 시)
     * @return mixed 렌더링된 뷰
     */
    public function form(Request $request, $id = null)
    {
        $influencer = null;
        $categories = [];
        
        if ($id) {
            $result = $this->influencerService->getInfluencerDetail($id);
            
            if (!$result['success']) {
                $this->setFlash('error', $result['message']);
                $this->redirect('/influencers');
                return;
            }
            
            $influencer = $result['data']['influencer'];
            $categories = $result['data']['categories'];
        }
        
        $platforms = Platform::all();
        $allCategories = Category::all();
        
        return $this->render('influencer/form', [
            'influencer' => $influencer,
            'selectedCategories' => $categories,
            'platforms' => $platforms,
            'allCategories' => $allCategories
        ]);
    }
    
    /**
     * 인플루언서 생성/업데이트를 처리합니다.
     * 
     * @param Request $request 요청 객체
     * @return void
     */
    public function save(Request $request)
    {
        $id = $request->getPost('id');
        
        $data = [
            'name' => $request->getPost('name', ''),
            'handle' => $request->getPost('handle', ''),
            'bio' => $request->getPost('bio', ''),
            'follower_count' => (int)$request->getPost('follower_count', 0),
            'engagement_rate' => (float)$request->getPost('engagement_rate', 0),
            'platform_id' => $request->getPost('platform_id'),
            'categories' => $request->getPost('categories', [])
        ];
        
        if ($id) {
            $data['id'] = $id;
        }
        
        $result = $this->influencerService->saveInfluencer($data);
        
        if ($result['success']) {
            $this->setFlash('success', $result['message']);
            $this->redirect('/influencers');
        } else {
            $this->setFlash('error', $result['message']);
            $this->redirect("/influencers/form" . ($id ? "/$id" : ""));
        }
    }
    
    /**
     * 인플루언서 삭제를 처리합니다.
     * 
     * @param Request $request 요청 객체
     * @param int $id 삭제할 인플루언서 ID
     * @return void
     */
    public function delete(Request $request, $id)
    {
        $result = $this->influencerService->deleteInfluencer($id);
        
        if ($result['success']) {
            $this->setFlash('success', $result['message']);
        } else {
            $this->setFlash('error', $result['message']);
        }
        
        $this->redirect('/influencers');
    }
} 