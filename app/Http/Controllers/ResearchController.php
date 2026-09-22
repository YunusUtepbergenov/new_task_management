<?php

namespace App\Http\Controllers;

use App\Models\Scraper;
use App\Traits\DownloadsPrivateFiles;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ResearchController extends Controller
{
    use DownloadsPrivateFiles;

    /**
     * @var array<int, string>
     */
    public const CATEGORIES = ['houses', 'jobs', 'cars', 'products', 'corruption'];

    public function scraping(){
        return view('page.research.scraping');
    }

    public function storeScrape(Request $request){
        $request->validate([
            'name' => 'required',
            'category' => ['required', Rule::in(self::CATEGORIES)],
            'file' => 'file|max:60000'
        ]);

        $file = $request->file('file');
        if($file){
            $filename = $request->file->getClientOriginalName();
            $upload = Storage::disk('local')->putFileAs('files/scraper/'.$request->category.'/', $file, $filename);
            if( !$upload ){
                return response()->json(['status' => 0,'msg'=>'Something went wrong, upload is failed.']);
            }else{
                Scraper::create([
                    'name' => $request->name,
                    'category' => $request->category,
                    'date' => $request->date,
                    'file' => $filename
                ]);
            }
        }else{
            Scraper::create([
                'name' => $request->name,
                'category' => $request->category,
                'date' => $request->date,
            ]);
        }

        return back();
    }

    public function download($id){
        $scrape = Scraper::findOrFail($id);
        abort_unless(in_array($scrape->category, self::CATEGORIES, true), 404);

        return $this->downloadPrivateFile('files/scraper/'.$scrape->category, $scrape->file);
    }
}
