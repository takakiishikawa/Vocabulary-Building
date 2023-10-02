<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Technology;

class AdminTechnologyController extends Controller
{
    public function index(){
        //technologyリストを全て取得
        //idとnameのみを返す
        $technologyList = Technology::select('id','name')->get();
        return response()->json([
            'technologyList' => $technologyList,
        ]);
    }

    public function save(Request $request){
        // Validation (add more rules as needed)
        $validatedData = $request->validate([
            'technologyList.*' => 'required|string|max:255', // Each technology item should be a string and have a maximum length of 255
        ]);
    
        $technologyList = $validatedData['technologyList'];
    
        // Fetch current technologies from DB
        $currentTechnologies = Technology::pluck('name')->toArray();
    
        // Find technologies to be deleted
        $technologiesToDelete = array_diff($currentTechnologies, $technologyList);
        foreach ($technologiesToDelete as $techName) {
            Technology::where('name', $techName)->delete();
        }
    
        // Find technologies to be added
        $technologiesToAdd = array_diff($technologyList, $currentTechnologies);
        foreach ($technologiesToAdd as $techName) {
            Technology::create(['name' => $techName]);
        }
    
        return response()->json(['message' => 'Technologies updated successfully']);
    }
    
}
