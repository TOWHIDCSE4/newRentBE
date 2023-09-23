<?php

namespace App\Models;

use AjCastro\Searchable\Searchable;
use App\Models\Base\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportPostViolation extends BaseModel
{
    use HasFactory;
    use Searchable;

    protected $guarded = [];

    protected $searchable = [
        'columns' => [
            'reason'
        ]
    ];

    protected $appends = [
        'user',
        'mo_post',
    ];


    public function getUserAttribute()
    {
        $userExist = User::where('id', $this->user_id)->first();
        if ($userExist != null) {
            return $userExist;
        }
        return null;
    }
    public function getMoPostAttribute()
    {
        $postExist = MoPost::where('id', $this->mo_post_id)->first();
        if ($postExist != null) {
            return $postExist;
        }
        return null;
    }
}
