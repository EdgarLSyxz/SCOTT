<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    public $timestamps = true;

    public const AREA_OTT = 'OTT';

    public const AREA_DTH = 'DTH';

    public static function getAreas(): array
    {
        return [self::AREA_OTT, self::AREA_DTH];
    }

    protected $fillable = [
        'type',
        'category',
        'title',
        'duration',
        'reported_by',
        'reviewed_by',
        'attended_by',
        'status',
        'area',
    ];

    public function channel()
    {
        return $this->belongsTo(Channel::class);
    }

    public function reportDetails()
    {
        return $this->hasMany(ReportDetail::class);
    }

    public function reportedBy()
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function attendedBy()
    {
        return $this->belongsTo(User::class, 'attended_by');
    }

    public function reviewedBy()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function stages()
    {
        return $this->hasManyThrough(Stage::class, ReportDetail::class, 'report_id', 'id', 'id', 'stage_id');
    }

    public function reportContentLosses()
    {
        return $this->hasManyThrough(
            ReportContentLoss::class,
            ReportDetail::class,
            'report_id',
            'report_detail_id',
            'id',
            'id'
        );
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function chromecast()
    {
        return $this->hasOne(Chromecast::class);
    }

    public function channelTests()
    {
        return $this->hasMany(ChannelTest::class);
    }

    public function deviceStoreAvailabilities()
    {
        return $this->hasMany(DeviceStoreAvailability::class);
    }

    public function canBeViewedBy($user): bool
    {
        if (! $user) {
            return false;
        }

        if ((int) ($user->id ?? 0) === 1) {
            return true;
        }

        $userArea = strtolower(trim($user->area ?? ''));
        $reportArea = strtolower(trim($this->area ?? ''));

        return $userArea !== '' && $userArea === $reportArea;
    }

    public function canBeEditedBy($user): bool
    {
        return $this->canBeViewedBy($user);
    }
}
