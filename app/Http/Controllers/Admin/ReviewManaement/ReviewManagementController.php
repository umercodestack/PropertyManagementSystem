<?php

namespace App\Http\Controllers\Admin\ReviewManaement;

use App\Http\Controllers\Controller;
use App\Models\GuestReview;
use App\Models\Review;
use App\Models\ReviewReply;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ReviewManagementController extends Controller
{
    
    public function __construct()
    {
        $this->middleware('permission');
    }
    
    public function index()
    {
        $reviews = Review::all();
        // dd($reviews);
        return view('Admin.review-management.index', ['reviews'=>$reviews]);
    }

    public function edit(Review $review_management)
    {
        $review = Review::whereId($review_management->id)->first();
        $replies = ReviewReply::where('review_id',$review_management->id)->get();
        return view('Admin.review-management.edit', ['review'=>$review, 'replies'=>$replies]);
    }

    public function update(Request $request,$id)
    {
        $user_id = Auth::user()->id;
        // $request->validate([
        //     'review_id' => 'required',
        //     'content' => 'content',
        // ]);

        $response = Http::withHeaders([
            'user-api-key' => env('CHANNEX_API_KEY'),
        ])->post(env('CHANNEX_URL') . "/api/v1/reviews/$id/reply", [
                    "reply" => [
                        "reply" =>$request->content
                    ]
                ]);
        if ($response->successful()) {
            ReviewReply::create([
                'review_id' => $id,
                'content' => $request->content,
                'reply_by' => $user_id
            ]);
        } else {
            $error = $response->body();
        }
        return redirect()->back();
    }

    public function guestReview($id)
    {
        $review = Review::whereId($id)->first();
        $guestReview = GuestReview::where('review_id',$review->id)->first();
        return view('Admin.review-management.guest-review', ['review' => $review, 'guestReview' => $guestReview]);
    }

    public function guestReviewStore(Request $request)
    {

        $id = $request->review_id;
        $respect_house_rules = $request->respect_house_rules;
        $communication = $request->communication;
        $cleanliness = $request->cleanliness;
        $response = Http::withHeaders([
            'user-api-key' => env('CHANNEX_API_KEY'),
        ])->post(env('CHANNEX_URL') . "/api/v1/reviews/$id/reply", [
                    "review" => [
                        "scores" => [
                            [
                                "category"=> "respect_house_rules",
                                "rating"=> $respect_house_rules
                            ],
                            [
                                "category"=> "communication",
                                "rating"=> $communication
                            ],
                            [
                                "category"=> "cleanliness",
                                "rating"=>$cleanliness
                            ],
                        ],
                        "private_review"=> $request->private_review,
                        "public_review"=> $request->public_review,
                        "is_reviewee_recommended"=> isset($request->is_reviewee_recommended) && $request->is_reviewee_recommended ? true : false,
                        "tags"=> [$request->tags]
                    ]
                ]);
        if ($response->successful()) {
            GuestReview::create([
                'review_id' => $id,
                'reply_by' => json_encode([
                    "scores" => [
                        [
                            "category"=> "respect_house_rules",
                            "rating"=> $respect_house_rules
                        ],
                        [
                            "category"=> "communication",
                            "rating"=> $communication
                        ],
                        [
                            "category"=> "cleanliness",
                            "rating"=>$cleanliness
                        ],
                    ],
                    "private_review"=> $request->private_review,
                    "public_review"=> $request->public_review,
                    "is_reviewee_recommended"=> isset($request->is_reviewee_recommended) && $request->is_reviewee_recommended ? true : false,
                    "tags"=> $request->tags
                ])
            ]);
        } else {
            $error = $response->body();
        }
        return redirect()->back();
    }
}
