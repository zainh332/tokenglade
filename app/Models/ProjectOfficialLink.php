<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectOfficialLink extends Model
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

    public function setWebsiteAttribute($value)
    {
        $this->attributes['website'] = $this->formatUrlWithProtocol($value);
    }

    public function setDocumentationAttribute($value)
    {
        $this->attributes['documentation'] = $this->formatUrlWithProtocol($value);
    }

    public function setWhitepaperAttribute($value)
    {
        $this->attributes['whitepaper'] = $this->formatUrlWithProtocol($value);
    }

    public function setGithubAttribute($value)
    {
        $this->attributes['github'] = $this->formatUrlWithProtocol($value);
    }

    public function setMediumAttribute($value)
    {
        $this->attributes['medium'] = $this->formatUrlWithProtocol($value);
    }
}
