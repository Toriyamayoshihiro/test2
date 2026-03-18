namespace App\Enums;

enum AttendanceRequestStatus: int
{
    case Pending = 0;
    case Approved = 1;
    case Rejected = 2;

    public function label(): string
    {
        return match ($this) {
            Status::Pending  => '承認待ち',
            Status::Approved => '承認',
            Status::Rejected => '却下'
        };
    }
}
