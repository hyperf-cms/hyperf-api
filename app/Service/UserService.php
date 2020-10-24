

namespace App\Service;

use Hyperf\DbConnection\Db;
use Hyperf\Utils\Context;

class UserService
{

    public function getInfoById($user_id)
    {
        $userInfo = Db::table('sy_users')->where('id', $user_id)->first();
        return $userInfo;
    }
}