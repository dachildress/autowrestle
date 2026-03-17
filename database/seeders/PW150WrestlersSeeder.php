<?php

namespace Database\Seeders;

use App\Models\Division;
use App\Models\DivGroup;
use App\Models\Tournament;
use App\Models\TournamentWrestler;
use App\Models\User;
use App\Models\Wrestler;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Seeds 150 wrestlers (70% boys, 30% girls), distributed across the PW division's
 * 6 groups for tournament 7, and registers them to that tournament.
 * Ensures user, clubs, tournament 7, PW division, and 6 groups exist.
 */
class PW150WrestlersSeeder extends Seeder
{
    private const TOURNAMENT_ID = 1;

    private const PW_DIVISION_NAME = 'PW';

    private const BOYS_COUNT = 105;

    private const GIRLS_COUNT = 45;

    private const TOTAL = 150;

    /** Boys groups: P-1, 2-3, 4-5. Girls same. Counts per group. */
    private const PER_BOYS_GROUP = 35;

    private const PER_GIRLS_GROUP = 15;

    public function run(): void
    {
        $userId = $this->ensureUser();
        $this->ensureClubs();
        $tournament = $this->ensureTournament();
        $division = $this->ensureDivision($tournament);
        $groups = $this->ensureGroups($division);

        if ($groups->count() < 6) {
            $this->command->warn('PW division does not have 6 groups (3 boys, 3 girls). Created ' . $groups->count() . ' group(s). Add girls groups in the UI or run a seeder that creates them.');

            // Distribute all 150 across whatever groups we have (e.g. 3 boys groups only)
            $this->createWrestlersAndRegister($userId, $tournament, $groups, $division);
            return;
        }

        $this->createWrestlersAndRegister($userId, $tournament, $groups, $division);
    }

    private function ensureUser(): int
    {
        $user = User::first();
        if (! $user) {
            $user = User::create([
                'name' => 'Seeder Admin',
                'email' => 'admin@autowrestle.local',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]);
        }

        return (int) $user->id;
    }

    private function ensureClubs(): void
    {
        if (DB::table('clubs')->count() > 0) {
            return;
        }
        $clubs = [
            'Amherst Quick Pin',
            'Jefferson Forest',
            'LCA',
            'Linkhorne',
            'Mat Pack',
            'Celtic Wrestling Club',
            'Rustburg',
            'Independent',
        ];
        foreach ($clubs as $i => $name) {
            DB::table('clubs')->insertOrIgnore([
                'id' => $i + 1,
                'Club' => $name,
            ]);
        }
    }

    private function ensureTournament(): Tournament
    {
        $tournament = Tournament::find(self::TOURNAMENT_ID);
        if ($tournament) {
            return $tournament;
        }
        $now = now()->format('Y-m-d H:i:s');
        DB::table('tournaments')->insert([
            'id' => self::TOURNAMENT_ID,
            'TournamentName' => 'Quick Pin K-12',
            'TournamentDate' => '2017-01-21',
            'link' => null,
            'message' => 'Seeded tournament.',
            'AllowDouble' => '1',
            'status' => 1,
            'OpenDate' => '2017-01-11',
            'ViewWrestlers' => 1,
            'Type' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return Tournament::find(self::TOURNAMENT_ID);
    }

    private function ensureDivision(Tournament $tournament): Division
    {
        $division = Division::where('Tournament_Id', self::TOURNAMENT_ID)
            ->where('DivisionName', self::PW_DIVISION_NAME)
            ->first();

        if ($division) {
            return $division;
        }
        $id = (int) Division::max('id') + 1;
        Division::create([
            'id' => $id,
            'DivisionName' => self::PW_DIVISION_NAME,
            'StartingMat' => 1,
            'TotalMats' => 4,
            'PerBracket' => 4,
            'Tournament_Id' => self::TOURNAMENT_ID,
            'bouted' => 0,
            'Bracketed' => 0,
            'printedbrackets' => 0,
            'printedbouts' => 0,
        ]);

        return Division::find($id);
    }

    /** @return \Illuminate\Support\Collection<int, DivGroup> */
    private function ensureGroups(Division $division): \Illuminate\Support\Collection
    {
        $groups = DivGroup::where('Tournament_Id', self::TOURNAMENT_ID)
            ->where('Division_id', $division->id)
            ->orderByRaw("CASE WHEN gender = 'boys' THEN 0 WHEN gender = 'girls' THEN 1 ELSE 2 END")
            ->orderBy('MinGrade')
            ->get();

        $groups = $groups->filter(fn ($g) => in_array((int) $g->MinGrade, [-1, 2, 4], true))->values();

        if ($groups->count() >= 6) {
            return $groups;
        }

        // Ensure we have 3 boys + 3 girls groups for PW
        $defs = [
            ['boys', 'Grades P - 1', 3, 7, -1, 1, 7, 1],
            ['boys', 'Grades 2 - 3', 7, 9, 2, 3, 7, 2],
            ['boys', 'Grades 4 - 5', 9, 11, 4, 5, 8, 3],
            ['girls', 'Grades P - 1', 3, 7, -1, 1, 17, 1],
            ['girls', 'Grades 2 - 3', 7, 9, 2, 3, 7, 2],
            ['girls', 'Grades 4 - 5', 9, 11, 4, 5, 8, 3],
        ];
        $nextId = (int) DivGroup::where('Tournament_Id', self::TOURNAMENT_ID)->where('Division_id', $division->id)->max('id') + 1;
        if ($nextId < 1) {
            $nextId = 1;
        }
        foreach ($defs as $def) {
            $exists = DivGroup::where('Tournament_Id', self::TOURNAMENT_ID)
                ->where('Division_id', $division->id)
                ->where('gender', $def[0])
                ->where('MinGrade', $def[4])
                ->exists();
            if (! $exists) {
                DivGroup::create([
                    'id' => $nextId,
                    'Name' => $def[1],
                    'gender' => $def[0],
                    'MinAge' => $def[2],
                    'MaxAge' => $def[3],
                    'MinGrade' => $def[4],
                    'MaxGrade' => $def[5],
                    'MaxWeightDiff' => $def[6],
                    'MaxExpDiff' => $def[7],
                    'MaxPwrDiff' => 0,
                    'BracketType' => '4',
                    'bracketed' => 0,
                    'bouted' => 0,
                    'Tournament_Id' => self::TOURNAMENT_ID,
                    'Division_id' => $division->id,
                ]);
                $nextId++;
            }
        }

        return DivGroup::where('Tournament_Id', self::TOURNAMENT_ID)
            ->where('Division_id', $division->id)
            ->orderByRaw("CASE WHEN gender = 'boys' THEN 0 WHEN gender = 'girls' THEN 1 ELSE 2 END")
            ->orderBy('MinGrade')
            ->get()
            ->filter(fn ($g) => in_array((int) $g->MinGrade, [-1, 2, 4], true))
            ->values();
    }

    private function createWrestlersAndRegister(
        int $userId,
        Tournament $tournament,
        \Illuminate\Support\Collection $groups,
        Division $division
    ): void {
        $faker = \Faker\Factory::create();
        $clubs = DB::table('clubs')->pluck('Club')->all();
        if (empty($clubs)) {
            $clubs = ['Independent'];
        }

        $groupList = $groups->values()->all();
        $nGroups = count($groupList);

        // Build assignment: which group index gets how many (boys then girls)
        $assignments = [];
        if ($nGroups >= 6) {
            $assignments = [
                0 => self::PER_BOYS_GROUP,
                1 => self::PER_BOYS_GROUP,
                2 => self::PER_BOYS_GROUP,
                3 => self::PER_GIRLS_GROUP,
                4 => self::PER_GIRLS_GROUP,
                5 => self::PER_GIRLS_GROUP,
            ];
        } else {
            $perGroup = (int) floor(self::TOTAL / $nGroups);
            $remainder = self::TOTAL % $nGroups;
            for ($i = 0; $i < $nGroups; $i++) {
                $assignments[$i] = $perGroup + ($i < $remainder ? 1 : 0);
            }
        }

        $groupIndex = 0;
        $leftInGroup = $assignments[0] ?? 0;
        $created = 0;

        for ($i = 0; $i < self::TOTAL; $i++) {
            if ($leftInGroup <= 0 && $groupIndex < $nGroups - 1) {
                $groupIndex++;
                $leftInGroup = $assignments[$groupIndex] ?? 0;
            }
            $group = $groupList[$groupIndex];
            $isBoy = in_array((string) $group->gender, ['boys', 'coed'], true) && $groupIndex < 3;
            if ($nGroups >= 6) {
                $isBoy = $groupIndex < 3;
            }
            $gender = $isBoy ? 'Boy' : 'Girl';

            $minAge = $group->MinAge;
            $maxAge = $group->MaxAge;
            $minGrade = $group->MinGrade;
            $maxGrade = $group->MaxGrade;
            $age = $minAge === $maxAge ? $minAge : random_int($minAge, $maxAge);
            $gradeNum = $minGrade === $maxGrade ? $minGrade : random_int($minGrade, $maxGrade);
            $gradeStr = $gradeNum === -1 ? 'P' : ($gradeNum === 0 ? 'K' : (string) $gradeNum);
            $weight = $age <= 7 ? random_int(35, 65) : ($age <= 11 ? random_int(50, 95) : random_int(70, 130));
            $years = random_int(0, min($age - 3, 5));

            $firstName = $gender === 'Boy' ? $faker->firstNameMale() : $faker->firstNameFemale();
            $lastName = $faker->lastName();
            $club = $clubs[array_rand($clubs)];

            $wr = Wrestler::create([
                'wr_first_name' => substr($firstName, 0, 30),
                'wr_last_name' => substr($lastName, 0, 30),
                'wr_gender' => $gender,
                'wr_club' => substr($club, 0, 30),
                'wr_age' => $age,
                'wr_grade' => $gradeStr,
                'wr_weight' => $weight,
                'wr_pr' => $weight,
                'wr_dob' => Carbon::now()->subYears($age)->subDays(random_int(0, 364)),
                'wr_wins' => 0,
                'wr_losses' => 0,
                'wr_years' => $years,
                'user_id' => $userId,
            ]);

            TournamentWrestler::create([
                'wr_first_name' => $wr->wr_first_name,
                'wr_last_name' => $wr->wr_last_name,
                'wr_club' => $wr->wr_club,
                'wr_age' => $wr->wr_age,
                'wr_grade' => $wr->wr_grade,
                'wr_weight' => $wr->wr_weight,
                'wr_pr' => $wr->wr_pr,
                'wr_dob' => $wr->wr_dob,
                'wr_wins' => 0,
                'wr_losses' => 0,
                'wr_years' => $wr->wr_years,
                'group_id' => $group->id,
                'Tournament_id' => self::TOURNAMENT_ID,
                'Wrestler_Id' => $wr->id,
                'bracketed' => 0,
            ]);

            $leftInGroup--;
            $created++;
        }

        $this->command->info("Created {$created} wrestlers and registered them to tournament " . self::TOURNAMENT_ID . ' (PW division).');
    }
}
