<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectSocialLink extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function profile()
    {
        return $this->belongsTo(ProjectProfile::class, 'project_profile_id');
    }

    protected function formatUrlWithProtocol($url)
    {
        if (!$url) return null;
        $url = trim($url);
        if ($url !== '' && !preg_match('/^https?:\/\//i', $url)) {
            return 'https://' . $url;
        }
        return $url;
    }

    public function setTwitterAttribute($value)
    {
        $this->attributes['twitter'] = $this->formatUrlWithProtocol($value);
    }

    public function setTelegramAttribute($value)
    {
        $this->attributes['telegram'] = $this->formatUrlWithProtocol($value);
    }

    public function setDiscordAttribute($value)
    {
        $this->attributes['discord'] = $this->formatUrlWithProtocol($value);
    }

    public function setLinkedinAttribute($value)
    {
        $this->attributes['linkedin'] = $this->formatUrlWithProtocol($value);
    }

    public function setRedditAttribute($value)
    {
        $this->attributes['reddit'] = $this->formatUrlWithProtocol($value);
    }

    public function setYoutubeAttribute($value)
    {
        $this->attributes['youtube'] = $this->formatUrlWithProtocol($value);
    }
}
