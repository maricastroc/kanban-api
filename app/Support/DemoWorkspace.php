<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Board;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Owns the shared "try it" demo account and its sample workspace.
 *
 * The app is fully server-side per authenticated user, so a demo needs a real
 * user with real rows. We keep a single shared account and rebuild its boards
 * from scratch on every entry — that guarantees each visitor lands on a clean,
 * populated workspace and that nothing a previous visitor did sticks around.
 */
final class DemoWorkspace
{
    public const EMAIL = 'demo@kanban.app';

    public const NAME = 'Demo User';

    /**
     * Resolve the demo user (creating it on first use) and reseed its boards.
     */
    public static function provision(): User
    {
        $user = User::firstOrCreate(
            ['email' => self::EMAIL],
            // No one logs in with this password — entry is through demoLogin —
            // so it just needs to be unguessable.
            ['name' => self::NAME, 'password' => Str::random(40)],
        );

        self::reset($user);

        return $user;
    }

    /**
     * Wipe the user's workspace and rebuild it from the blueprint.
     */
    public static function reset(User $user): void
    {
        DB::transaction(function () use ($user): void {
            // Mass deletes skip Eloquent events (so Board's "activate a sibling
            // on delete" hook never fires) and lean on the DB foreign keys to
            // cascade columns -> tasks -> subtasks -> task_tag for us.
            Board::query()->where('user_id', $user->id)->delete();
            Tag::query()->where('user_id', $user->id)->delete();

            $blueprint = self::blueprint();

            $tagIds = [];
            foreach ($blueprint['tags'] as $name => $color) {
                $tagIds[$name] = Tag::create([
                    'name' => $name,
                    'color' => $color,
                    'user_id' => $user->id,
                ])->id;
            }

            foreach ($blueprint['boards'] as $boardData) {
                $board = Board::create([
                    'name' => $boardData['name'],
                    'user_id' => $user->id,
                    'is_active' => $boardData['active'],
                ]);

                foreach ($boardData['columns'] as $columnData) {
                    // `order` is auto-assigned (max + 1) by the Column model, so
                    // creating them in array order yields a stable 1..n layout.
                    $column = $board->columns()->create(['name' => $columnData['name']]);

                    foreach ($columnData['tasks'] ?? [] as $taskData) {
                        $task = $column->tasks()->create([
                            'name' => $taskData['name'],
                            'description' => $taskData['description'] ?? null,
                            'due_date' => isset($taskData['due'])
                                ? now()->addDays($taskData['due'])
                                : null,
                        ]);

                        foreach ($taskData['subtasks'] ?? [] as [$subtaskName, $isCompleted]) {
                            $subtask = $task->subtasks()->create(['name' => $subtaskName]);

                            // The Subtask "creating" hook forces is_completed to
                            // false, so seeded completions have to be flipped
                            // after the insert.
                            if ($isCompleted) {
                                $subtask->update(['is_completed' => true]);
                            }
                        }

                        if (! empty($taskData['tags'])) {
                            $task->tags()->sync(
                                array_map(static fn (string $name): int => $tagIds[$name], $taskData['tags'])
                            );
                        }
                    }
                }
            }
        });
    }

    /**
     * The sample data. Kept intentionally rich so the demo shows off the app:
     * multiple boards, overdue / upcoming / no due dates, partially completed
     * subtasks and colour-coded tags.
     *
     * `due` is a day offset from now (negative = overdue); omit it for no date.
     * Each subtask is a [name, isCompleted] pair.
     *
     * @return array{tags: array<string, string>, boards: array<int, array<string, mixed>>}
     */
    private static function blueprint(): array
    {
        return [
            'tags' => [
                'Design' => '#49C4E5',
                'Feature' => '#8471F2',
                'Bug' => '#EA5555',
                'Research' => '#20C997',
                'DevOps' => '#F2B84B',
                'Docs' => '#6C8CFF',
            ],
            'boards' => [
                [
                    'name' => 'Platform Launch',
                    'active' => true,
                    'columns' => [
                        [
                            'name' => 'Todo',
                            'tasks' => [
                                [
                                    'name' => 'Design the settings panel',
                                    'description' => 'Account, theme and notification preferences in one place.',
                                    'due' => 6,
                                    'tags' => ['Design', 'Feature'],
                                    'subtasks' => [
                                        ['Audit the current settings', true],
                                        ['Wireframe the layout', true],
                                        ['Hi-fi mockups', false],
                                        ['Dark mode pass', false],
                                    ],
                                ],
                                [
                                    'name' => 'Add board search to the sidebar',
                                    'description' => 'Filter boards by name as the list grows.',
                                    'due' => 12,
                                    'tags' => ['Feature'],
                                    'subtasks' => [
                                        ['Debounced input', false],
                                        ['Empty state', false],
                                    ],
                                ],
                                [
                                    'name' => 'Research transactional email providers',
                                    'description' => 'Compare Resend, Postmark and Brevo for deliverability and price.',
                                    'due' => -3,
                                    'tags' => ['Research'],
                                ],
                            ],
                        ],
                        [
                            'name' => 'Doing',
                            'tasks' => [
                                [
                                    'name' => 'Build the onboarding flow',
                                    'description' => 'First-run experience backed by a seeded sample board.',
                                    'due' => 2,
                                    'tags' => ['Feature'],
                                    'subtasks' => [
                                        ['Welcome screen', true],
                                        ['Seed sample data', true],
                                        ['Guided tooltip tour', false],
                                    ],
                                ],
                                [
                                    'name' => 'Fix drag-and-drop offset on Safari',
                                    'description' => 'Cards land one slot below the drop target.',
                                    'tags' => ['Bug'],
                                    'subtasks' => [
                                        ['Reproduce on Safari', true],
                                        ['Patch sensor activation', false],
                                    ],
                                ],
                            ],
                        ],
                        [
                            'name' => 'Done',
                            'tasks' => [
                                [
                                    'name' => 'Migrate auth to httpOnly cookies',
                                    'description' => 'Move the Sanctum token out of localStorage to mitigate XSS.',
                                    'tags' => ['Feature'],
                                    'subtasks' => [
                                        ['Set the cookie on login', true],
                                        ['Promote cookie to a Bearer header', true],
                                        ['Drop the localStorage token', true],
                                    ],
                                ],
                                [
                                    'name' => 'Set up the CI pipeline',
                                    'description' => 'Lint, test and build on every pull request.',
                                    'tags' => ['DevOps'],
                                    'subtasks' => [
                                        ['Lint step', true],
                                        ['Test step', true],
                                        ['Build step', true],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'name' => 'Marketing Site',
                    'active' => false,
                    'columns' => [
                        [
                            'name' => 'Backlog',
                            'tasks' => [
                                [
                                    'name' => 'Write the launch blog post',
                                    'due' => 20,
                                    'tags' => ['Docs'],
                                ],
                                [
                                    'name' => 'Record a 60s demo video',
                                    'description' => 'Walk through creating a board, a task and dragging it across columns.',
                                    'tags' => ['Design'],
                                ],
                            ],
                        ],
                        [
                            'name' => 'In Progress',
                            'tasks' => [
                                [
                                    'name' => 'Design the landing hero',
                                    'description' => 'Above-the-fold section with the tagline and a clear call to action.',
                                    'due' => 4,
                                    'tags' => ['Design'],
                                    'subtasks' => [
                                        ['Copy draft', true],
                                        ['Hero illustration', false],
                                    ],
                                ],
                            ],
                        ],
                        [
                            'name' => 'Shipped',
                            'tasks' => [
                                [
                                    'name' => 'Reserve the domain',
                                    'tags' => ['DevOps'],
                                    'subtasks' => [
                                        ['Buy the domain', true],
                                        ['Point the DNS', true],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
