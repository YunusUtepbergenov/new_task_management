<?php

namespace App\Http\Controllers;

use App\Models\Scraper;
use Illuminate\Http\Request;

class ResearchController extends Controller
{
    public function scraping(){
        return view('page.research.scraping');
    }

    public function storeScrape(Request $request){
        $request->validate([
            'name' => 'required',
            'category' => 'required',
            'file' => 'file|max:40000'
        ]);

        $file = $request->file('file');
        $filename = $request->file->getClientOriginalName();
        // $file = $request->file;

        $upload = $file->move(public_path("scraper/".$request->category.'/'), $filename);

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
        return back();
    }

    public function download($id){
        $scrape = Scraper::where('id', $id)->first();
        $file = public_path("scraper/".$scrape->category.'/'.$scrape->file);

        return response()->download($file);
    }
}
