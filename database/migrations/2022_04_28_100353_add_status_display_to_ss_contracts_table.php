<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStatusDisplayToSsContractsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ss_contracts', function (Blueprint $table) {
            $table->string('status_display', 50)->after('status')->nullable()->comment('"Active" - a normal, active contract|

"Lifetime Access" - a contract that has the one time purchase flag set to true |

"Active Until Next Bill"- contract is still active, but will be cancelled on their next billing date |

"Access Removed" - contract is cancelled, we removed the tag |

"Expired" - contract reached the max order threshold and is now expired - access has been removed |

"Paused" - we don’t have this right now, but we’ll need a way to temporarily remove access for a customer without cancelling');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ss_contracts', function (Blueprint $table) {
            //
        });
    }
}
