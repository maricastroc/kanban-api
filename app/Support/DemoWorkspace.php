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
    public const EMAIL = 'demo@cadence.app';

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

            // The frontend resolves a tag's colour by *name* (see getTagHex), so
            // the stored colour must be a palette name, not a hex value.
            $tagIds = [];
            foreach ($blueprint['tags'] as $name => $colorName) {
                $tagIds[$name] = Tag::create([
                    'name' => $name,
                    'color' => $colorName,
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
                            'is_completed' => $taskData['completed'] ?? false,
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
     * several full columns, a healthy mix of overdue / due-soon / future / done
     * dates, partially completed subtasks and colour-coded tags.
     *
     * `due` is a day offset from now (negative = overdue); omit it for no date.
     * `completed` marks the whole task done. Each subtask is a [name, isDone]
     * pair. Tag colours are palette *names* (resolved by the frontend's
     * getTagHex), not hex values — the palette has 8 colours, so 8 tags max.
     *
     * @return array{tags: array<string, string>, boards: array<int, array<string, mixed>>}
     */
    private static function blueprint(): array
    {
        return [
            'tags' => [
                'Design' => 'Aqua Blue',
                'Feature' => 'Lavender',
                'Bug' => 'Vivid Red',
                'Research' => 'Mint Green',
                'DevOps' => 'Golden Yellow',
                'Docs' => 'Blue',
                'Frontend' => 'Soft Pink',
                'Backend' => 'Rose Red',
            ],
            'boards' => [
                [
                    'name' => 'Platform Launch',
                    'active' => true,
                    'columns' => [
                        [
                            'name' => 'Backlog',
                            'tasks' => [
                                [
                                    'name' => 'Research transactional email providers',
                                    'description' => 'Compare Resend, Postmark and Brevo for deliverability and price.',
                                    'due' => -3,
                                    'tags' => ['Research', 'Backend'],
                                ],
                                [
                                    'name' => 'Explore an activity log',
                                    'description' => 'A per-board feed of recent changes.',
                                    'due' => 25,
                                    'tags' => ['Feature'],
                                ],
                                [
                                    'name' => 'Evaluate a mobile app shell',
                                    'due' => 30,
                                    'tags' => ['Research', 'Frontend'],
                                ],
                                [
                                    'name' => 'Add board templates',
                                    'description' => 'Let users spin up a board from a preset (sprint, content calendar…).',
                                    'due' => 18,
                                    'tags' => ['Feature'],
                                    'subtasks' => [
                                        ['Define preset format', false],
                                        ['Template picker UI', false],
                                    ],
                                ],
                                [
                                    'name' => 'Investigate offline support',
                                    'due' => 40,
                                    'tags' => ['Research'],
                                ],
                            ],
                        ],
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
                                    'tags' => ['Feature', 'Frontend'],
                                    'subtasks' => [
                                        ['Debounced input', false],
                                        ['Empty state', false],
                                    ],
                                ],
                                [
                                    'name' => 'Build the tag manager',
                                    'due' => 9,
                                    'tags' => ['Feature', 'Frontend'],
                                    'subtasks' => [
                                        ['Create / edit / delete', false],
                                        ['Per-board usage counts', false],
                                    ],
                                ],
                                [
                                    'name' => 'Add keyboard shortcuts',
                                    'description' => 'Search focus, quick-add task, close dialogs.',
                                    'due' => 14,
                                    'tags' => ['Feature'],
                                ],
                                [
                                    'name' => 'Create empty-state illustrations',
                                    'due' => 7,
                                    'tags' => ['Design'],
                                ],
                            ],
                        ],
                        [
                            'name' => 'In Progress',
                            'tasks' => [
                                [
                                    'name' => 'Build the onboarding flow',
                                    'description' => 'First-run experience backed by a seeded sample board.',
                                    'due' => 1,
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
                                    'due' => 0,
                                    'tags' => ['Bug', 'Frontend'],
                                    'subtasks' => [
                                        ['Reproduce on Safari', true],
                                        ['Patch sensor activation', false],
                                    ],
                                ],
                                [
                                    'name' => 'Implement column reordering',
                                    'due' => 3,
                                    'tags' => ['Feature', 'Frontend'],
                                    'subtasks' => [
                                        ['Horizontal sortable context', true],
                                        ['Persist new order', false],
                                    ],
                                ],
                                [
                                    'name' => 'Wire up the REST API for tasks',
                                    'due' => 5,
                                    'tags' => ['Backend', 'Feature'],
                                    'subtasks' => [
                                        ['CRUD endpoints', true],
                                        ['Reorder + move', false],
                                    ],
                                ],
                                [
                                    'name' => 'Add optimistic updates',
                                    'description' => 'Update the UI immediately and reconcile with the server.',
                                    'due' => 2,
                                    'tags' => ['Frontend'],
                                ],
                            ],
                        ],
                        [
                            'name' => 'In Review',
                            'tasks' => [
                                [
                                    'name' => 'Add real-time updates with WebSocket',
                                    'description' => 'Live board sync so collaborators see moves instantly.',
                                    'due' => 4,
                                    'tags' => ['Feature', 'Backend'],
                                    'subtasks' => [
                                        ['Socket server', true],
                                        ['Reconnect logic', false],
                                    ],
                                ],
                                [
                                    'name' => 'Review the auth middleware',
                                    'due' => 2,
                                    'tags' => ['Backend', 'Bug'],
                                ],
                                [
                                    'name' => 'Polish the loading states',
                                    'due' => 6,
                                    'tags' => ['Design', 'Frontend'],
                                ],
                                [
                                    'name' => 'Audit accessibility',
                                    'description' => 'Focus order, ARIA labels and colour contrast.',
                                    'due' => 8,
                                    'tags' => ['Research', 'Frontend'],
                                    'subtasks' => [
                                        ['Keyboard navigation', true],
                                        ['Contrast pass', false],
                                    ],
                                ],
                                [
                                    'name' => 'Code review: boards context',
                                    'due' => 1,
                                    'tags' => ['Frontend'],
                                ],
                            ],
                        ],
                        [
                            'name' => 'Done',
                            'tasks' => [
                                [
                                    'name' => 'Migrate auth to httpOnly cookies',
                                    'description' => 'Move the Sanctum token out of localStorage to mitigate XSS.',
                                    'due' => -10,
                                    'completed' => true,
                                    'tags' => ['Feature', 'Backend'],
                                    'subtasks' => [
                                        ['Set the cookie on login', true],
                                        ['Promote cookie to a Bearer header', true],
                                        ['Drop the localStorage token', true],
                                    ],
                                ],
                                [
                                    'name' => 'Set up the CI pipeline',
                                    'description' => 'Lint, test and build on every pull request.',
                                    'due' => -5,
                                    'completed' => true,
                                    'tags' => ['DevOps'],
                                    'subtasks' => [
                                        ['Lint step', true],
                                        ['Test step', true],
                                        ['Build step', true],
                                    ],
                                ],
                                [
                                    'name' => 'Implement light / dark theme',
                                    'due' => -8,
                                    'completed' => true,
                                    'tags' => ['Design', 'Frontend'],
                                    'subtasks' => [
                                        ['Theme tokens', true],
                                        ['Persist preference', true],
                                    ],
                                ],
                                [
                                    'name' => 'Add the boards sidebar',
                                    'due' => -12,
                                    'completed' => true,
                                    'tags' => ['Frontend'],
                                ],
                                [
                                    'name' => 'Set up the database schema',
                                    'due' => -15,
                                    'completed' => true,
                                    'tags' => ['Backend', 'DevOps'],
                                    'subtasks' => [
                                        ['Boards / columns / tasks', true],
                                        ['Subtasks / tags', true],
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
                            'name' => 'Ideas',
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
                                [
                                    'name' => 'Draft the pricing page copy',
                                    'due' => 22,
                                    'tags' => ['Docs'],
                                ],
                                [
                                    'name' => 'Plan a Product Hunt launch',
                                    'due' => 28,
                                    'tags' => ['Research'],
                                ],
                                [
                                    'name' => 'Collect early testimonials',
                                    'due' => 35,
                                    'tags' => ['Docs'],
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
                                [
                                    'name' => 'Build the features section',
                                    'due' => 6,
                                    'tags' => ['Frontend'],
                                    'subtasks' => [
                                        ['Layout', true],
                                        ['Feature icons', false],
                                    ],
                                ],
                                [
                                    'name' => 'Set up analytics',
                                    'due' => 3,
                                    'tags' => ['DevOps'],
                                ],
                                [
                                    'name' => 'Write SEO meta tags',
                                    'due' => 5,
                                    'tags' => ['Docs', 'Frontend'],
                                ],
                                [
                                    'name' => 'Create social preview images',
                                    'due' => 7,
                                    'tags' => ['Design'],
                                ],
                            ],
                        ],
                        [
                            'name' => 'Shipped',
                            'tasks' => [
                                [
                                    'name' => 'Reserve the domain',
                                    'due' => -14,
                                    'completed' => true,
                                    'tags' => ['DevOps'],
                                    'subtasks' => [
                                        ['Buy the domain', true],
                                        ['Point the DNS', true],
                                    ],
                                ],
                                [
                                    'name' => 'Set up the landing repo',
                                    'due' => -16,
                                    'completed' => true,
                                    'tags' => ['DevOps', 'Frontend'],
                                ],
                                [
                                    'name' => 'Choose a font pairing',
                                    'due' => -18,
                                    'completed' => true,
                                    'tags' => ['Design'],
                                ],
                                [
                                    'name' => 'Wireframe the homepage',
                                    'due' => -20,
                                    'completed' => true,
                                    'tags' => ['Design'],
                                    'subtasks' => [
                                        ['Sketch sections', true],
                                        ['Low-fi in Figma', true],
                                    ],
                                ],
                                [
                                    'name' => 'Set up the newsletter',
                                    'due' => -12,
                                    'completed' => true,
                                    'tags' => ['Feature'],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
