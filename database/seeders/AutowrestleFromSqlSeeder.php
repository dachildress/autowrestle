<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeds tournaments, divisions, divgroups, clubs (teams), and wrestlers from autowrestle.sql.
 * Run after migrations and ensure user id=1 exists (e.g. InsertAdminUser or manual).
 * Wrestlers are assigned user_id=1 so the admin user owns them.
 */
class AutowrestleFromSqlSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now()->format('Y-m-d H:i:s');

        if (DB::table('tournamenttypes')->count() === 0) {
            DB::table('tournamenttypes')->insert(['id' => 1, 'Name' => 'Round Robin']);
        }

        if (DB::table('clubs')->count() === 0) {
            $this->seedClubs();
        }

        if (DB::table('tournaments')->count() === 0) {
            $this->seedTournaments($now);
        }

        if (DB::table('divisions')->count() === 0) {
            $this->seedDivisions($now);
        }

        if (DB::table('divgroups')->count() === 0) {
            $this->seedDivgroups($now);
        }

        if (DB::table('wrestlers')->count() === 0) {
            $this->seedWrestlers($now);
        }
    }

    private function seedClubs(): void
    {
        $clubs = [
            ['id' => 1, 'Club' => 'Amherst Quick Pin'],
            ['id' => 2, 'Club' => 'Bees Club'],
            ['id' => 3, 'Club' => 'Buffalo Gap'],
            ['id' => 4, 'Club' => 'Cave Springs'],
            ['id' => 5, 'Club' => 'Cougar Crush'],
            ['id' => 6, 'Club' => 'Daniels Elem'],
            ['id' => 7, 'Club' => 'DMS'],
            ['id' => 9, 'Club' => 'Fighting Blues'],
            ['id' => 13, 'Club' => 'Franklin County'],
            ['id' => 16, 'Club' => 'Glen Cove'],
            ['id' => 17, 'Club' => 'Greene County'],
            ['id' => 18, 'Club' => 'Halifax Youth'],
            ['id' => 19, 'Club' => 'Hidden Valley'],
            ['id' => 20, 'Club' => 'Hilltoppers (ECG)'],
            ['id' => 21, 'Club' => 'Jaguars Linkhorne'],
            ['id' => 22, 'Club' => 'Jefferson Forest'],
            ['id' => 23, 'Club' => 'LCA'],
            ['id' => 24, 'Club' => 'Liberty (Bedford)'],
            ['id' => 27, 'Club' => 'Linkhorne'],
            ['id' => 29, 'Club' => 'Mat Pack'],
            ['id' => 30, 'Club' => 'Northside Middle'],
            ['id' => 34, 'Club' => 'Roanoke Gladiators'],
            ['id' => 35, 'Club' => 'Rockingham County'],
            ['id' => 36, 'Club' => 'Rustburg'],
            ['id' => 38, 'Club' => 'Smith Mtn Lake'],
            ['id' => 82, 'Club' => 'Individual'],
            ['id' => 109, 'Club' => 'Independent'],
            ['id' => 127, 'Club' => 'Forest'],
        ];
        foreach (array_chunk($clubs, 50) as $chunk) {
            DB::table('clubs')->insertOrIgnore($chunk);
        }
    }

    private function seedTournaments(string $now): void
    {
        DB::table('tournaments')->insert([
            [
                'id' => 1,
                'TournamentName' => 'Linkhorne Middle Schools',
                'TournamentDate' => '2016-12-03',
                'link' => 'LinkhorneMiddleSchools2016-12-03.pdf',
                'message' => "Cost: \$20 - Additional weight class or div. \$10\nBreakfast and Concessions will be available\n\nMadison Weight Classes: Grouped by weight, age, and experience\n\nPre-Registration is required by 8:00 pm on Friday, Dec. 2nd on www.autowrestle.com\nUSA Wrestling membership is required: www.usawmembership.com",
                'AllowDouble' => '1',
                'status' => 1,
                'OpenDate' => '2016-11-21',
                'ViewWrestlers' => 1,
                'Type' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 2,
                'TournamentName' => 'Bees Club',
                'TournamentDate' => '2016-12-10',
                'link' => 'BeesClub2016-12-10.pdf',
                'message' => "Brookville High School, 100 Laxton Road Lynchburg, 24502\n\nWeigh-Ins: 7 am – 8:30 am WRESTLING BEGINS AT 9:30 AM\n\nDivisions: Elementary K – 5th grade / Middle School 6th – 8th grade",
                'AllowDouble' => '1',
                'status' => 1,
                'OpenDate' => '2016-12-04',
                'ViewWrestlers' => 1,
                'Type' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 3,
                'TournamentName' => 'Rustburg Open',
                'TournamentDate' => '2016-12-17',
                'link' => 'RustburgOpen2016-12-17.pdf',
                'message' => "Rustburg High School; 1671 Village Highway; Rustburg, VA 24588\n\nWEIGH-IN: Scales open @ 7:30 am. Weigh-in at 8:00 am. COST: \$20.00 per wrestler\n\nWRESTLING: All ages start time – approx. 9:00 am",
                'AllowDouble' => '1',
                'status' => 0,
                'OpenDate' => '2016-12-04',
                'ViewWrestlers' => 1,
                'Type' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 4,
                'TournamentName' => 'E.C. Glass',
                'TournamentDate' => '2016-12-31',
                'link' => 'E.C.Glass2016-12-31.pdf',
                'message' => "Cost: \$20 - Additional weight class or div. \$10\n\nConcessions will be available\n\nMadison Weight Classes. Pre-Registration required.",
                'AllowDouble' => '1',
                'status' => 1,
                'OpenDate' => '2016-12-25',
                'ViewWrestlers' => 1,
                'Type' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 5,
                'TournamentName' => 'Smith Mountain Lake Open',
                'TournamentDate' => '2017-01-07',
                'link' => 'SmithMountainLakeOpen2016-02-07.pdf',
                'message' => "Weigh-Ins: 7:00 to 8:30 AM (Grades K-5) / 10:00-11:30 AM (Grades 6-8)\n\nCost: \$20 - Additional weight class or div. \$10",
                'AllowDouble' => '1',
                'status' => 1,
                'OpenDate' => '2017-01-01',
                'ViewWrestlers' => 1,
                'Type' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 6,
                'TournamentName' => 'HYWC Winter Invitational',
                'TournamentDate' => '2017-01-14',
                'link' => 'HYWCWinterInvitational2017-01-14.pdf',
                'message' => "Registration: Deadline 8 pm Friday January 13, 2017. Register online at www.autowrestle.com.\n\nCost: \$20.00 per wrestler. Format: Folkstyle - Round Robin\n\nDivisions: Pee Wee K-5 / Juniors 6-8",
                'AllowDouble' => '1',
                'status' => 2,
                'OpenDate' => '2017-01-08',
                'ViewWrestlers' => 1,
                'Type' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 7,
                'TournamentName' => 'Quick Pin K-12',
                'TournamentDate' => '2017-01-21',
                'link' => 'QuickPinK-122017-01-21.pdf',
                'message' => "Amherst County High School, 139 Lancer Lane, Amherst, Va 24521\n\nEntry Fee: \$20.00. Weigh-Ins: 7:00 to 9:00 am.\n\n4 Divisions: 2 Peewee (K-5), JR 6-8, SR 9-12",
                'AllowDouble' => '1',
                'status' => 1,
                'OpenDate' => '2017-01-11',
                'ViewWrestlers' => 1,
                'Type' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    private function seedDivisions(string $now): void
    {
        $rows = [
            [1, 'PW', 1, 4, 4, 1, 1, 1, 0, 0],
            [2, 'JR', 5, 3, 5, 1, 1, 1, 0, 0],
            [3, 'PW', 1, 4, 4, 2, 1, 1, 0, 0],
            [4, 'JR', 5, 3, 5, 2, 1, 1, 0, 0],
            [6, 'PW', 1, 4, 4, 3, 0, 0, 0, 0],
            [7, 'JR', 5, 3, 5, 3, 0, 0, 0, 0],
            [13, 'PW', 1, 5, 4, 4, 1, 1, 0, 0],
            [14, 'JR', 6, 2, 5, 4, 1, 1, 0, 0],
            [16, 'PW', 1, 4, 4, 5, 0, 1, 0, 0],
            [17, 'JR', 5, 3, 5, 5, 0, 0, 0, 0],
            [19, 'PW', 1, 4, 4, 6, 1, 1, 0, 0],
            [20, 'JR', 5, 2, 4, 6, 0, 0, 0, 0],
            [22, 'PW', 1, 4, 4, 7, 0, 0, 0, 0],
            [23, 'JR', 5, 3, 5, 7, 0, 0, 0, 0],
            [25, 'SR', 9, 1, 5, 7, 0, 0, 0, 0],
        ];
        foreach ($rows as $r) {
            DB::table('divisions')->insertOrIgnore([
                'id' => $r[0],
                'DivisionName' => $r[1],
                'StartingMat' => $r[2],
                'TotalMats' => $r[3],
                'PerBracket' => $r[4],
                'Tournament_Id' => $r[5],
                'bouted' => $r[6],
                'Bracketed' => $r[7],
                'printedbrackets' => $r[8],
                'printedbouts' => $r[9],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    private function seedDivgroups(string $now): void
    {
        $rows = [
            [1, 'Grades P - 1', 3, 7, -1, 1, 7, '4', 0, 1, 1, 1, 1, 1],
            [2, 'Grades 2 - 3', 7, 9, 2, 3, 7, '4', 0, 1, 1, 2, 1, 1],
            [3, 'Grades 4 - 5', 9, 11, 4, 5, 8, '4', 0, 1, 1, 3, 1, 1],
            [4, 'Grades 6 - 8', 11, 15, 6, 8, 8, '5', 2, 1, 1, 3, 1, 2],
            [5, 'Grades P - 1', 3, 7, -1, 1, 7, '4', 0, 1, 1, 1, 2, 3],
            [6, 'Grades 2 - 3', 7, 9, 2, 3, 7, '4', 0, 1, 1, 2, 2, 3],
            [7, 'Grades 4 - 5', 9, 11, 4, 5, 8, '4', 0, 1, 1, 3, 2, 3],
            [8, 'Grades 6 - 8', 11, 15, 6, 8, 8, '5', 2, 1, 1, 3, 2, 4],
            [12, 'Grades P - 1', 3, 7, -1, 1, 7, '4', 0, 0, 0, 1, 3, 6],
            [13, 'Grades 2 - 3', 7, 9, 2, 3, 7, '4', 0, 0, 0, 2, 3, 6],
            [14, 'Grades 4 - 5', 9, 11, 4, 5, 8, '4', 0, 0, 0, 3, 3, 6],
            [15, 'Grades 6 - 8', 11, 15, 6, 8, 8, '5', 2, 0, 0, 3, 3, 7],
            [27, 'Grades P - 1', 3, 7, -1, 1, 7, '4', 0, 1, 1, 1, 4, 13],
            [28, 'Grades 2 - 3', 7, 9, 2, 3, 7, '4', 0, 1, 1, 2, 4, 13],
            [29, 'Grades 4 - 5', 9, 11, 4, 5, 8, '4', 0, 1, 1, 3, 4, 13],
            [30, 'Grades 6 - 8', 11, 15, 6, 8, 8, '5', 2, 1, 1, 3, 4, 14],
            [41, 'Grades P - 1', 3, 7, -1, 1, 7, '4', 0, 1, 1, 1, 6, 19],
            [42, 'Grades 2 - 3', 7, 9, 2, 3, 7, '4', 0, 1, 1, 2, 6, 19],
            [43, 'Grades 4 - 5', 9, 11, 4, 5, 8, '4', 0, 1, 1, 3, 6, 19],
            [44, 'Grades 6 - 8', 11, 15, 6, 8, 8, '5', 2, 0, 0, 3, 6, 20],
            [48, 'Grades P - 1', 3, 7, -1, 1, 7, '4', 0, 0, 0, 1, 7, 22],
            [49, 'Grades 2 - 3', 7, 9, 2, 3, 7, '4', 0, 0, 0, 2, 7, 22],
            [50, 'Grades 4 - 5', 9, 11, 4, 5, 8, '4', 0, 0, 0, 3, 7, 22],
            [51, 'Grades 6 - 8', 11, 15, 6, 8, 8, '5', 2, 0, 0, 3, 7, 23],
            [55, 'Grades 9-12', 13, 18, 9, 12, 10, '6', 10, 0, 0, 4, 7, 25],
        ];
        foreach ($rows as $r) {
            DB::table('divgroups')->insertOrIgnore([
                'id' => $r[0],
                'Name' => $r[1],
                'MinAge' => $r[2],
                'MaxAge' => $r[3],
                'MinGrade' => $r[4],
                'MaxGrade' => $r[5],
                'MaxWeightDiff' => $r[6],
                'BracketType' => $r[7],
                'MaxPwrDiff' => $r[8],
                'bracketed' => $r[9],
                'bouted' => $r[10],
                'MaxExpDiff' => $r[11],
                'Tournament_Id' => $r[12],
                'Division_id' => $r[13],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    private function seedWrestlers(string $now): void
    {
        $userId = 1; // Assign all to admin user for testing
        $wrestlers = [
            [1, 'Connor', 'Childress', 'Amherst Quick Pin', 12, '7', 150, 152, null, 0, 0, 3, $userId],
            [5, 'Brandon', 'Noell', 'Celtic Wrestling Club', 11, '5', 75, 70, null, 0, 0, 5, $userId],
            [6, 'Cameron', 'Noell', 'Celtic Wrestling Club', 9, '4', 65, 56, null, 0, 0, 4, $userId],
            [8, 'Carson', 'Meadows', 'LCA', 11, '6', 95, 95, null, 0, 0, 5, $userId],
            [10, 'Carter', 'Shipp', 'Jefferson Forest', 13, '8', 199, 145, null, 0, 0, 7, $userId],
            [11, 'Toby', 'Schoffstall', 'LCA', 11, '6', 117, 104, null, 0, 0, 4, $userId],
            [20, 'Peyton', 'Hatcher', 'Mat Pack', 11, '5', 75, 68, null, 0, 0, 3, $userId],
            [22, 'Jamon', 'Hubbard', 'Linkhorne', 13, '8', 192, 162, null, 0, 0, 0, $userId],
            [32, 'Jake', 'Lee', 'Jefferson Forest', 12, '7', 171, 154, null, 0, 0, 5, $userId],
            [47, 'Grant', 'Moyer', 'Celtic Wrestling Club', 6, 'K', 47, 42, null, 0, 0, 1, $userId],
        ];
        foreach ($wrestlers as $w) {
            if (count($w) < 13) {
                continue;
            }
            DB::table('wrestlers')->insertOrIgnore([
                'id' => $w[0],
                'wr_first_name' => $w[1],
                'wr_last_name' => $w[2],
                'wr_club' => $w[3],
                'wr_age' => $w[4],
                'wr_grade' => $w[5],
                'wr_weight' => $w[6],
                'wr_pr' => $w[7],
                'wr_dob' => $w[8],
                'wr_wins' => $w[9],
                'wr_losses' => $w[10],
                'wr_years' => $w[11],
                'user_id' => $w[12],
                'usawnumber' => null,
                'coach_name' => '',
                'coach_phone' => '',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
