<?php

namespace App\Models;

use App\Enums\Users\RoleEnum;
use App\Lib\Helpers\FileStorageHelper;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use LaravelActivityLogs\Traits\HasActivityLog;

/**
 * @property integer                         $id                Id.
 * @property string                          $first_name        Firstname.
 * @property string                          $last_name         Lastname.
 * @property string                          $email             Email.
 * @property string                          $picture           Path of the account's picture.
 * @property string                          $password          Password.
 * @property \App\Enums\Users\RoleEnum       $role              Role.
 * @property integer                         $order             Order.
 * @property boolean                         $published         Published status.
 * @property \Illuminate\Support\Carbon|null $published_at      Publication status update date.
 * @property \Illuminate\Support\Carbon|null $email_verified_at Email verified date.
 * @property-read \Illuminate\Support\Carbon $created_at        Created date.
 * @property-read \Illuminate\Support\Carbon $updated_at        Updated date.
 *
 * @method static void booted()                          Perform any actions required after the model boots.
 * @method static void updatePublishedStatus(self $user) Check if the authenticable user can update the
 * published status.
 * @method static void setImage(self $user)              Set model's account's picture.
 * @method static void setOrder(self $user)              Set model's order after the last element of the list.
 * @method static void updatePassword(self $user)        Update model's password.
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|\LaravelActivityLogs\Models\ActivityLog[] $activityLogs
 * Get Activities of the User (has-many relationship).
 */
class User extends Authenticatable
{
    use HasActivityLog;
    use HasFactory;
    use Notifiable;

    /**
     * The attributes that are fillable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'picture',
        'password',
        'role',
        'order',
        'published',
        'published_at',
        'email_verified_at',
    ];

    /**
     * Cast the property to an enum.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'role'              => RoleEnum::class,
        'email_verified_at' => 'datetime',
        'published'         => 'boolean',
        'published_at'      => 'datetime',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Perform any actions required after the model boots.
     *
     * @return void
     */
    protected static function booted(): void
    {
        static::creating(function (self $user) {
            self::updatePassword($user);
            self::setOrder($user);
            self::setImage($user);
            self::checkElevationPrivileges($user);
        });
        static::updating(function (self $user) {
            self::updatePassword($user);
            self::setImage($user);
            self::updatePublishedStatus($user);
            self::checkElevationPrivileges($user);
        });
        static::updated(function (self $user) {
            FileStorageHelper::removeOldFile($user, 'picture');
        });
        static::deleted(function (self $user) {
            FileStorageHelper::removeFile($user, 'picture');
        });
    }

    // * METHODS

    /**
     * Check if the authenticable user can update the published status.
     *
     * @param self $user
     * @return void
     */
    private static function updatePublishedStatus(self $user): void
    {
        if (optional(auth('backend')->user())->getKey() === $user->getKey()) {
            \validator(
                ['published'          => $user->published],
                ['published'          => 'required|boolean|accepted'],
                ['published.accepted' => trans('validation.rule.disable_own_account')],
            )->validate();
        }
    }

    /**
     * Prevent user elevation privileges.
     *
     * @param self $user
     * @return void
     */
    private static function checkElevationPrivileges(self $user): void
    {
        throw_if(
            auth('backend')->user() and auth('backend')->user()->role->value() > $user->role->value(),
            AuthorizationException::class
        );
    }

    /**
     * Set model's account's picture.
     *
     * @param self $user
     * @return void
     */
    private static function setImage(self $user): void
    {
        $user->picture = FileStorageHelper::storeFile($user, $user->picture, true);
    }

    /**
     * Set model's order after the last element of the list.
     *
     * @param self $user
     * @return void
     */
    private static function setOrder(self $user): void
    {
        $user->order = \intval(self::query()->max('order')) + 1;
    }

    /**
     * Update model's password.
     *
     * @param self $user
     * @return void
     */
    private static function updatePassword(self $user): void
    {
        if ($user->password != null and Hash::needsRehash($user->password)) {
            $user->password = Hash::make($user->password);
        } else {
            $user->password = $user->getOriginal('password');
        }
    }
}
