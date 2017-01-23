<?php

namespace Orchid\Foundation\Http\Controllers\Tools;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Orchid\Foundation\Core\Models\Menu;
use Orchid\Foundation\Facades\Dashboard;

class MenuController extends Controller
{

    /**
     * @var
     */
    public  $lang;

    /**
     * @var
     */
    public  $menu;


    /**
     * @param Request $request
     *
     * @return mixed
     */
    public function index(Request $request)
    {
        return view('dashboard::container.tools.menu.listing', [
            'menu'    => collect(config('content.menu')),
            'locales' => collect(config('content.locales')),
        ]);
    }


    /**
     * @param $nameMenu
     * @param Request $request
     * @return View
     */
    public function show($nameMenu, Request $request)
    {
        $currentLocale = $request->get('lang',App::getLocale());
        $staticPage = Dashboard::routeMenu()->all();

        $menu = Menu::where('lang',$currentLocale)
            ->whereNull('parent')
            ->where('type',$nameMenu)->with('children')->get();


        return view('dashboard::container.tools.menu.menu', [
            'nameMenu' => $nameMenu,
            'locales' => config('content.locales'),
            'currentLocale' => $currentLocale,
            'menu' => $menu,
            'staticPage' => $staticPage
        ]);
    }


    /**
     * @param $menu
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update($menu,Request $request)
    {
        $this->lang = $request->get('lang');
        $this->menu = $menu;


         Menu::where('lang',$this->lang)
            ->where('type',$menu)
            ->delete();

        $this->createMenuElement($request->get('data'));


        return response()->json([
            'title' => 'Успешно',
            'message' => 'Данные сохранены',
            'type' => 'success'
        ]);
    }




    private function createMenuElement(array $items, $parent = null){

        foreach ($items as $item){
            unset($item['id']);
            $item['lang'] = $this->lang;
            $item['type'] = $this->menu;
            $item['parent'] = $parent;
            $menu = Menu::create($item);

            if(key_exists('children',$item)){
                $this->createMenuElement($item['children'],$menu->id);
            }

        }


    }



}
