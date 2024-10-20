<?php
namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Extension extends Model
{
    use HasFactory;
    protected $appends = ['shortCodes'];
    protected $guarded = ['id'];

    protected $casts = [
        'shortcode' => 'object',
    ];

    public function getShortCodesAttribute() {
        $shortCode = $this->shortcode;
        foreach ($shortCode as $key => $item) {
            $titleKey =  $item->title;
            $shortCode->$key->title = trans($titleKey);
        }
        return $shortCode;
    }
}
