<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterCode extends Model
{
    use HasFactory;

    protected $fillable = ['code','qr_image','user_id'];

    /**
     * Method createCode
     *
     * @return void
     */
    public function createCode($post)
    {
        $code = $post['code'];
        $this->code = $code;
        $this->user_id = auth()->user()->id;
        $this->qr_image = generateQRCode($code);
        $this->save();
        return $this;
    }

    public function updateCode($code)
    {
        $masterCode = $this->first();
        if (!$masterCode) {
            return false;
        }
        $masterCode->code = $code;
        $masterCode->qr_image = generateQRCode($code);
        $masterCode->save();
        return $masterCode;
    }

    /**
     * Method user
     *
     * @return void
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
