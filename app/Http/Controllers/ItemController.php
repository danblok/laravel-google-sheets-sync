<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Setting;
use App\Services\GoogleSheetsService;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ItemController extends Controller
{
    /**
     * @return View|Factory
     */
    public function index(Request $request)
    {
        $items = Item::paginate(20);
        $googleSheetUrl = Setting::get('google_sheet_url', '');
        // Can be dynamically changed, but has to do disk operation,
        // so it can slow down a reques especially if there are many of them
        $credentialsPath = storage_path(env('GOOGLE_APPLICATION_CREDENTIALS'));
        if (! $credentialsPath || ! file_exists($credentialsPath)) {
            throw new \Exception('Google credentials file not found. Path: '.$credentialsPath.':'.$credentialsPath);
        }
        $googleClientEmail = json_decode(File::get($credentialsPath), true)['client_email'] ?? '';

        return view('items.index', compact('items', 'googleSheetUrl', 'googleClientEmail'));
    }

    /**
     * @return View|Factory
     */
    public function create()
    {
        return view('items.create');
    }

    /**
     * @return RedirectResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:Allowed,Prohibited',
        ]);

        Item::create($request->all());

        return redirect()->route('items.index')->with('success', 'Запись успешно создана');
    }

    /**
     * @return View|Factory
     */
    public function show(Item $item)
    {
        return view('items.show', compact('item'));
    }

    /**
     * @return View|Factory
     */
    public function edit(Item $item)
    {
        return view('items.edit', compact('item'));
    }

    /**
     * @return RedirectResponse
     */
    public function update(Request $request, Item $item)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:Allowed,Prohibited',
        ]);

        $item->update($request->all());

        return redirect()->route('items.index')->with('success', 'Запись успешно обновлена');
    }

    /**
     * @return RedirectResponse
     */
    public function destroy(Item $item)
    {
        $item->delete();

        return redirect()->route('items.index')->with('success', 'Запись успешно удалена');
    }

    /**
     * @return RedirectResponse
     */
    public function generate()
    {
        $statuses = ['Allowed', 'Prohibited'];

        for ($i = 0; $i < 1000; $i++) {
            Item::create([
                'name' => 'Item '.Str::random(10),
                'description' => 'Generated description '.Str::random(20),
                'status' => $statuses[array_rand($statuses)],
            ]);
        }

        return redirect()->route('items.index')->with('success', '1000 записей успешно созданы');
    }

    /**
     * @return RedirectResponse
     */
    public function clear()
    {
        Item::truncate();

        return redirect()->route('items.index')->with('success', 'Все записи успешно удалены');
    }

    /**
     * @return RedirectResponse
     */
    public function updateGoogleSheetUrl(Request $request)
    {
        $request->validate([
            'google_sheet_url' => 'required|url',
        ]);

        Setting::set('google_sheet_url', $request->google_sheet_url);

        return redirect()->route('items.index')->with('success', 'Ссылка на Google таблицу успешно обновлена');
    }
