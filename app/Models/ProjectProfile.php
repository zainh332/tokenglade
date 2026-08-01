<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectProfile extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function verifiedProject()
    {
        return $this->belongsTo(VerifiedProject::class, 'verified_project_id');
    }

    public function officialLinks()
    {
        return $this->hasOne(ProjectOfficialLink::class, 'project_profile_id');
    }

    public function socialLinks()
    {
        return $this->hasOne(ProjectSocialLink::class, 'project_profile_id');
    }

    public function officialWallets()
    {
        return $this->hasMany(ProjectOfficialWallet::class, 'project_profile_id');
    }
}
