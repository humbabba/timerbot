<x-layouts.app>
    <div class="max-w-4xl mx-auto px-4 py-8">

        @php
            $user = auth()->user();
            $canCreateTimers = $user?->hasPermission('timers.create');
            $canViewUsers = $user?->hasPermission('users.view');
            $canViewRoles = $user?->hasPermission('roles.view');
            $canViewTrash = $user?->hasPermission('trash.view');
            $canViewLogs = $user?->hasPermission('activity-logs.view');
            $canManageSettings = $user?->hasPermission('settings.manage');
            $isAuth = (bool) $user;
            $n = 0;
        @endphp

        {{-- Title --}}
        <h1 class="text-3xl md:text-4xl mb-2" style="font-family: var(--font-display);">
            User Manual
        </h1>
        <p class="text-text-muted text-sm mb-8" style="font-family: var(--font-display);">
            v{{ config('app.version', '0.00.00.01') }}
        </p>

        {{-- Table of Contents --}}
        @php $tocN = 0; @endphp
        <nav class="timerbot-panel p-4 md:p-6 mb-8">
            <h2 class="text-lg uppercase tracking-wider text-timerbot-green mb-4" style="font-family: var(--font-display);">
                Table of Contents
            </h2>
            <ol class="list-decimal list-inside space-y-1 text-sm">
                <li><a href="#overview" class="text-timerbot-teal hover:text-timerbot-lime">Overview</a></li>
                <li><a href="#getting-started" class="text-timerbot-teal hover:text-timerbot-lime">Getting Started</a></li>
                <li><a href="#timers" class="text-timerbot-teal hover:text-timerbot-lime">Timers</a></li>
                @auth
                    <li><a href="#running-a-timer" class="text-timerbot-teal hover:text-timerbot-lime">Running a Timer</a></li>
                @endauth
                <li><a href="#participant-view" class="text-timerbot-teal hover:text-timerbot-lime">Participant View</a></li>
                @if($canViewUsers)
                    <li><a href="#user-management" class="text-timerbot-teal hover:text-timerbot-lime">User Management</a></li>
                @endif
                @auth
                    <li><a href="#groups" class="text-timerbot-teal hover:text-timerbot-lime">Groups</a></li>
                @endauth
                @if($canViewTrash)
                    <li><a href="#trash-recovery" class="text-timerbot-teal hover:text-timerbot-lime">Trash &amp; Recovery</a></li>
                @endif
                @if($canViewLogs)
                    <li><a href="#activity-log" class="text-timerbot-teal hover:text-timerbot-lime">Activity Log</a></li>
                @endif
                @if($canManageSettings)
                    <li><a href="#settings" class="text-timerbot-teal hover:text-timerbot-lime">Settings</a></li>
                @endif
                <li><a href="#appearance" class="text-timerbot-teal hover:text-timerbot-lime">Appearance</a></li>
                @auth
                    <li><a href="#keyboard-shortcuts" class="text-timerbot-teal hover:text-timerbot-lime">Keyboard Shortcuts</a></li>
                @endauth
            </ol>
        </nav>

        {{-- Sections --}}
        <div class="space-y-12">

            {{-- Overview --}}
            <section id="overview" class="pt-4">
                <h2 class="text-xl uppercase tracking-wider text-timerbot-green mb-4 border-b border-subtle pb-2" style="font-family: var(--font-display);">
                    {{ ++$n }}. Overview
                </h2>
                <div class="space-y-3 text-sm leading-relaxed">
                    <p>
                        {{ config('app.name', 'Timerbot') }} is a collaborative meeting timer designed to keep meetings on track.
                        An operator creates a timer by setting a meeting end time and the number of participants, then runs the timer during the meeting.
                        The available time is divided equally among all participants, and each participant's countdown is displayed in real time.
                    </p>
                    <p>
                        Participants can view their countdown on a public display page &mdash; no login required for public timers.
                        The operator controls the flow: starting the timer, advancing to the next participant, going back to the previous one, pausing, and stopping.
                    </p>
                    <p>
                        Additional features include configurable audio warnings, custom participant terminology, overtime auto-reset,
                        group-based access control, role-based permissions, activity logging, soft-delete with trash and restore,
                        sortable table columns, light/dark mode, and app-wide settings.
                    </p>
                </div>
            </section>

            {{-- Getting Started --}}
            <section id="getting-started" class="border-t border-subtle mt-4 pt-2">
                <h2 class="text-xl uppercase tracking-wider text-timerbot-green mb-4 border-b border-subtle pb-2" style="font-family: var(--font-display);">
                    {{ ++$n }}. Getting Started
                </h2>
                <div class="space-y-4 text-sm leading-relaxed">
                    <h3 class="text-timerbot-teal uppercase tracking-wide" style="font-family: var(--font-display);">Browsing as a Guest</h3>
                    <p>
                        You can browse all <strong>public</strong> timers without logging in. The Timers page is accessible to everyone
                        and shows all public timers. You can also view the participant display for any public timer by visiting its URL directly.
                    </p>

                    @guest
                        <h3 class="text-timerbot-teal uppercase tracking-wide" style="font-family: var(--font-display);">Registration</h3>
                        <p>
                            Navigate to the <strong>Register</strong> page and enter your name and email address.
                            A one-time login link will be sent to your email &mdash; click it to sign in for the first time.
                            Your avatar is pulled from <a href="https://gravatar.com" target="_blank" class="text-timerbot-teal hover:text-timerbot-lime">Gravatar</a> based on your email.
                        </p>

                        <h3 class="text-timerbot-teal uppercase tracking-wide" style="font-family: var(--font-display);">Logging In</h3>
                        <p>
                            {{ config('app.name', 'Timerbot') }} uses <strong>magic link</strong> authentication &mdash; no passwords.
                            Enter your email on the Login page and a one-time login link will be sent to your inbox.
                            Click the link to sign in. The link expires after 15 minutes.
                        </p>
                    @endguest

                    <h3 class="text-timerbot-teal uppercase tracking-wide" style="font-family: var(--font-display);">Navigation</h3>
                    <p>
                        The top navigation bar provides access to all areas of the app:
                    </p>
                    <ul class="list-disc list-inside ml-4 space-y-1">
                        <li><strong>Home</strong> &mdash; The welcome page displaying news and announcements.</li>
                        <li><strong>Timers</strong> &mdash; View, create, and manage timers.</li>
                        @auth
                            <li><strong>Utils</strong> &mdash; A dropdown with links to Users, Roles, Trash, Settings, and Activity Log (visible based on your permissions).</li>
                            <li><strong>Profile</strong> &mdash; Your avatar and name appear on the right; click to view your profile or log out.</li>
                        @endauth
                    </ul>
                    <p>
                        On mobile devices the navigation collapses into a hamburger menu.
                    </p>
                </div>
            </section>

            {{-- Timers --}}
            <section id="timers" class="border-t border-subtle mt-4 pt-2">
                <h2 class="text-xl uppercase tracking-wider text-timerbot-green mb-4 border-b border-subtle pb-2" style="font-family: var(--font-display);">
                    {{ ++$n }}. Timers
                </h2>
                <div class="space-y-4 text-sm leading-relaxed">

                    <h3 class="text-timerbot-teal uppercase tracking-wide" style="font-family: var(--font-display);">Timer List</h3>
                    <p>
                        The Timers page shows all timers you have access to. Guests see only public timers.
                        Logged-in users see public timers plus any private timers belonging to their groups.
                        App admins see all timers. Use the search box and date-range filters to find specific timers.
                    </p>
                    @auth
                        <p>
                            Logged-in users can toggle between <strong>My Timers</strong> (only timers in your groups) and <strong>All Timers</strong>.
                            The app remembers your preference between visits.
                        </p>
                    @endauth
                    <p>
                        Click any column header (Name, Visibility, End time, or Participants) to sort the table.
                        Click the same header again to reverse the sort direction. The Status, Group, and Actions columns are not sortable.
                    </p>

                    @if($canCreateTimers)
                        <h3 class="text-timerbot-teal uppercase tracking-wide" style="font-family: var(--font-display);">Creating a Timer</h3>
                        <p>
                            Click <strong>Add Timer</strong> to create a new timer. Fill in the following fields:
                        </p>
                        <ul class="list-disc list-inside ml-4 space-y-1">
                            <li><strong>Name</strong> &mdash; A descriptive name for the timer (required).</li>
                            <li><strong>Visibility</strong> &mdash; <em>Public</em> (anyone with the link can view) or <em>Private</em> (only group members and app admins).</li>
                            <li><strong>Group</strong> &mdash; Create a new group or select an existing one. Groups control who can run and manage the timer.</li>
                            <li><strong>End time</strong> &mdash; The time the meeting should end (HH:MM format).</li>
                            <li><strong>Participants</strong> &mdash; The number of participants (1&ndash;999).</li>
                            <li><strong>Participant Term</strong> &mdash; Custom labels for participants (singular and plural), e.g. "presenter" / "presenters". Defaults to "speaker" / "speakers".</li>
                            <li><strong>Overtime Limit</strong> &mdash; How many minutes past the meeting end time the timer is allowed to run before automatically resetting to idle (1&ndash;59 minutes). This prevents a forgotten timer from running indefinitely.</li>
                            <li><strong>Warnings</strong> &mdash; Audio alerts that fire at configurable offsets relative to each participant's time running out. Choose from sounds like Alarm, Bell, Beep, Chime, Ding, Twang, and Warning. Use the preview button to hear each sound.</li>
                            <li><strong>Participant Message</strong> &mdash; Optional rich-text message displayed to participants on the public view page.</li>
                        </ul>

                        <h3 class="text-timerbot-teal uppercase tracking-wide" style="font-family: var(--font-display);">Warning Countdown Values</h3>
                        <p>
                            Each warning has a <strong>countdown value</strong> in seconds. A positive value (e.g. 60) means the warning fires
                            60 seconds <em>before</em> the participant's time expires. A value of 0 fires exactly when time runs out.
                            A negative value (e.g. &minus;30) fires 30 seconds <em>after</em> the participant's time has expired.
                            Values range from &minus;3600 to 3600.
                        </p>
                    @endif

                    @auth
                        <h3 class="text-timerbot-teal uppercase tracking-wide" style="font-family: var(--font-display);">Editing &amp; Copying</h3>
                        <p>
                            Group admins and app admins can edit a timer's settings at any time. Use the <strong>Copy</strong> action
                            to duplicate a timer along with its settings &mdash; a new group will be created for the copy.
                        </p>

                        <h3 class="text-timerbot-teal uppercase tracking-wide" style="font-family: var(--font-display);">Deleting Timers</h3>
                        <p>
                            Deleting a timer moves it to the <a href="#trash-recovery" class="text-timerbot-teal hover:text-timerbot-lime">Trash</a>.
                            It can be restored from there, or permanently deleted.
                        </p>
                    @endauth
                </div>
            </section>

            {{-- Running a Timer --}}
            @auth
                <section id="running-a-timer" class="border-t border-subtle mt-4 pt-2">
                    <h2 class="text-xl uppercase tracking-wider text-timerbot-green mb-4 border-b border-subtle pb-2" style="font-family: var(--font-display);">
                        {{ ++$n }}. Running a Timer
                    </h2>
                    <div class="space-y-4 text-sm leading-relaxed">

                        <h3 class="text-timerbot-teal uppercase tracking-wide" style="font-family: var(--font-display);">Operator Controls</h3>
                        <p>
                            Click <strong>Run Timer</strong> from the timer list or the participant view page.
                            The run page is the operator's control panel.
                        </p>
                        <ul class="list-disc list-inside ml-4 space-y-1">
                            <li><strong>Start</strong> &mdash; Begins the timer and starts the first participant's countdown.</li>
                            <li><strong>Next</strong> &mdash; Records the current participant's time and advances to the next one. Remaining meeting time is redistributed equally among the remaining participants.</li>
                            <li><strong>Previous</strong> &mdash; Goes back to the previous participant. Their entry is removed from the history and their countdown resumes where it left off.</li>
                            <li><strong>Undo</strong> &mdash; After advancing to the next participant, an Undo button appears with a countdown (up to 10 seconds). Click it to reverse the advance and return to the previous participant as if Next was never pressed.</li>
                            <li><strong>Pause / Resume</strong> &mdash; Freezes the current participant's countdown. While paused, time does not count against the participant. Pause duration is tracked and accounted for.</li>
                            <li><strong>Stop</strong> &mdash; Ends the timer session and resets to idle state.</li>
                        </ul>

                        <h3 class="text-timerbot-teal uppercase tracking-wide" style="font-family: var(--font-display);">Timer Locking</h3>
                        <p>
                            Only one operator can run a timer at a time. When you open the run page, you acquire a lock on the timer.
                            If another user tries to run the same timer, they will see a message indicating who currently holds the lock.
                        </p>
                        <p>
                            The lock is maintained with a heartbeat every 3 seconds. If your connection drops or you close the page,
                            the lock expires after 30 seconds, allowing another operator to take over. If the lock is taken by
                            someone else while you are on the run page, a "Lock Lost" overlay appears with a link back to the timer.
                        </p>

                        <h3 class="text-timerbot-teal uppercase tracking-wide" style="font-family: var(--font-display);">Live Settings</h3>
                        <p>
                            While running, you can adjust settings on the fly:
                        </p>
                        <ul class="list-disc list-inside ml-4 space-y-1">
                            <li><strong>End time</strong> &mdash; Shift the meeting end time. The per-participant allocation recalculates instantly.</li>
                            <li><strong>Participant Count</strong> &mdash; Add or reduce participants (cannot reduce below the current participant + 1). Time is redistributed automatically.</li>
                        </ul>

                        <h3 class="text-timerbot-teal uppercase tracking-wide" style="font-family: var(--font-display);">Meeting Countdown</h3>
                        <p>
                            The top of the run page shows a countdown to the meeting end time and the calculated time per person.
                            If the meeting end time is in the past, the app assumes the next occurrence (i.e. the following day).
                        </p>

                        <h3 class="text-timerbot-teal uppercase tracking-wide" style="font-family: var(--font-display);">Participant Panel</h3>
                        <p>
                            The central panel shows the current participant number (e.g. "Speaker 3 of 10", using your custom participant term) and a large countdown timer.
                            The timer is green when the participant is on time, and turns red with a pulsing animation when they go over.
                        </p>

                        <h3 class="text-timerbot-teal uppercase tracking-wide" style="font-family: var(--font-display);">Participant History</h3>
                        <p>
                            After the first participant finishes, a history table appears showing each completed participant's allotted time,
                            actual time used, and whether they were on time or over. Over-time participants are highlighted.
                        </p>

                        <h3 class="text-timerbot-teal uppercase tracking-wide" style="font-family: var(--font-display);">Audio Warnings</h3>
                        <p>
                            Configured warnings fire automatically at the specified countdown values during each participant's turn.
                            Sounds are generated using the Web Audio API &mdash; no external audio files are needed.
                            Make sure your browser's sound is not muted.
                        </p>

                        <h3 class="text-timerbot-teal uppercase tracking-wide" style="font-family: var(--font-display);">Overtime Auto-Reset</h3>
                        <p>
                            If the timer is still running after the meeting end time plus the configured <strong>overtime limit</strong>,
                            the timer automatically resets to idle. This prevents forgotten timers from running indefinitely.
                            The overtime limit is configurable per timer (1&ndash;59 minutes, default 5).
                        </p>

                        <h3 class="text-timerbot-teal uppercase tracking-wide" style="font-family: var(--font-display);">State Persistence</h3>
                        <p>
                            Timer state is saved to the server every 3 seconds. If you refresh the page or lose your connection,
                            the timer will resume from its last saved state.
                        </p>
                    </div>
                </section>
            @endauth

            {{-- Participant View --}}
            <section id="participant-view" class="border-t border-subtle mt-4 pt-2">
                <h2 class="text-xl uppercase tracking-wider text-timerbot-green mb-4 border-b border-subtle pb-2" style="font-family: var(--font-display);">
                    {{ ++$n }}. Participant View
                </h2>
                <div class="space-y-4 text-sm leading-relaxed">

                    <h3 class="text-timerbot-teal uppercase tracking-wide" style="font-family: var(--font-display);">What Participants See</h3>
                    <p>
                        The participant view is a read-only display that updates in real time. It shows:
                    </p>
                    <ul class="list-disc list-inside ml-4 space-y-1">
                        <li>Timer name and running status (running, paused, completed, or not running).</li>
                        <li>Stats bar: total participants, current time, end time, and meeting time remaining.</li>
                        <li>Time allotted per participant (recalculates live as participants complete their turns).</li>
                        <li>Current participant number and their countdown timer (using the custom participant term).</li>
                        <li>The optional participant message (rich text).</li>
                    </ul>
                    <p>
                        If the current participant goes over time, the per-participant time display flashes red to indicate
                        that remaining participants' allocations are decreasing.
                    </p>

                    <h3 class="text-timerbot-teal uppercase tracking-wide" style="font-family: var(--font-display);">Sharing the Timer URL</h3>
                    <p>
                        Share the timer's URL with participants. For <strong>public</strong> timers, anyone with the link
                        can view the countdown &mdash; no login required. For <strong>private</strong> timers, viewers must
                        be logged-in group members or app admins.
                    </p>

                    <h3 class="text-timerbot-teal uppercase tracking-wide" style="font-family: var(--font-display);">Keep Screen Awake</h3>
                    <p>
                        The participant view includes a <strong>"Keep screen awake"</strong> checkbox.
                        Enabling this uses the browser's Wake Lock API to prevent the screen from dimming or locking
                        while the timer is displayed &mdash; useful when projecting the timer on a shared screen.
                        The lock is automatically re-acquired if you switch tabs and return.
                    </p>

                    <h3 class="text-timerbot-teal uppercase tracking-wide" style="font-family: var(--font-display);">Real-Time Updates</h3>
                    <p>
                        The participant view polls the server every 2 seconds for state changes and renders countdowns
                        every 200ms for a smooth display. All time calculations are performed client-side for accuracy.
                    </p>
                </div>
            </section>

            {{-- User Management --}}
            @if($canViewUsers)
                <section id="user-management" class="border-t border-subtle mt-4 pt-2">
                    <h2 class="text-xl uppercase tracking-wider text-timerbot-green mb-4 border-b border-subtle pb-2" style="font-family: var(--font-display);">
                        {{ ++$n }}. User Management
                    </h2>
                    <div class="space-y-4 text-sm leading-relaxed">
                        <p>
                            Users with the appropriate permissions can manage user accounts from <strong>Utils &rarr; Users</strong>.
                        </p>

                        <h3 class="text-timerbot-teal uppercase tracking-wide" style="font-family: var(--font-display);">User List</h3>
                        <p>
                            The user list shows all registered users with their name, email, role(s), registration date, and last login.
                            Use search and date-range filters to find specific users. Click any sortable column header
                            (Name, Email, Created, Last Login) to sort the table; click again to reverse direction.
                        </p>

                        <h3 class="text-timerbot-teal uppercase tracking-wide" style="font-family: var(--font-display);">Editing Users</h3>
                        <p>
                            Edit a user's name, email, and assigned role. You can only edit users whose roles your role is allowed to assign
                            (see <em>Assignable Roles</em> below).
                            Each user can view and edit their own profile, including setting a preferred <strong>Starting View</strong>
                            (the page that loads after login).
                        </p>

                        @if($canViewRoles)
                            <h3 class="text-timerbot-teal uppercase tracking-wide" style="font-family: var(--font-display);">Roles &amp; Permissions</h3>
                            <p>
                                Roles group permissions together. Navigate to <strong>Utils &rarr; Roles</strong> to manage roles.
                                Each role can be granted granular permissions for timers, users, roles, trash, settings, and activity logs.
                                The built-in <strong>App Admin</strong> role has full access to everything.
                            </p>
                            <p>
                                Available permission categories:
                            </p>
                            <ul class="list-disc list-inside ml-4 space-y-1">
                                <li><strong>Timers</strong> &mdash; view, create, edit, delete</li>
                                <li><strong>Users</strong> &mdash; view, create, edit, delete</li>
                                <li><strong>Roles</strong> &mdash; view, create, edit, delete</li>
                                <li><strong>Trash</strong> &mdash; view, restore, delete</li>
                                <li><strong>Settings</strong> &mdash; manage</li>
                                <li><strong>Activity Logs</strong> &mdash; view</li>
                            </ul>

                            <h3 class="text-timerbot-teal uppercase tracking-wide" style="font-family: var(--font-display);">Assignable Roles</h3>
                            <p>
                                Each role defines which other roles it is allowed to assign to users. This prevents privilege escalation &mdash;
                                for example, a "Manager" role can be configured to assign the "User" role but not the "App Admin" role.
                                When creating or editing a role, use the <strong>Assignable Roles</strong> section to select which roles
                                users with this role can assign.
                            </p>
                            <p>
                                The Edit button on the user list only appears for users whose roles fall within your assignable roles.
                            </p>
                        @endif
                    </div>
                </section>
            @endif

            {{-- Groups --}}
            @auth
                <section id="groups" class="border-t border-subtle mt-4 pt-2">
                    <h2 class="text-xl uppercase tracking-wider text-timerbot-green mb-4 border-b border-subtle pb-2" style="font-family: var(--font-display);">
                        {{ ++$n }}. Groups
                    </h2>
                    <div class="space-y-4 text-sm leading-relaxed">
                        <p>
                            Groups control who can access and manage timers. Every timer belongs to exactly one group.
                        </p>

                        <h3 class="text-timerbot-teal uppercase tracking-wide" style="font-family: var(--font-display);">How Groups Work</h3>
                        <ul class="list-disc list-inside ml-4 space-y-1">
                            <li>When you create a timer, you either create a new group or select an existing one.</li>
                            <li>The timer creator is automatically added as a <strong>group admin</strong>.</li>
                            <li>You can add other users as members by searching for them by name or email.</li>
                            <li>Groups can be reused across multiple timers.</li>
                        </ul>

                        <h3 class="text-timerbot-teal uppercase tracking-wide" style="font-family: var(--font-display);">Admin vs. Member</h3>
                        <ul class="list-disc list-inside ml-4 space-y-1">
                            <li><strong>Group Members</strong> can run the timer and view private timers belonging to the group.</li>
                            <li><strong>Group Admins</strong> can do everything members can, plus edit and delete timers in the group and manage group membership.</li>
                        </ul>
                        <p>
                            App admins always have full access to all timers regardless of group membership.
                        </p>
                    </div>
                </section>
            @endauth

            {{-- Trash & Recovery --}}
            @if($canViewTrash)
                <section id="trash-recovery" class="border-t border-subtle mt-4 pt-2">
                    <h2 class="text-xl uppercase tracking-wider text-timerbot-green mb-4 border-b border-subtle pb-2" style="font-family: var(--font-display);">
                        {{ ++$n }}. Trash &amp; Recovery
                    </h2>
                    <div class="space-y-4 text-sm leading-relaxed">
                        <p>
                            When you delete a timer, user, or role, it is <strong>soft-deleted</strong> &mdash; moved to the Trash rather than permanently destroyed.
                            Navigate to <strong>Utils &rarr; Trash</strong> to manage deleted items.
                        </p>

                        <h3 class="text-timerbot-teal uppercase tracking-wide" style="font-family: var(--font-display);">Viewing Trash</h3>
                        <p>
                            The trash list shows all soft-deleted items with their type, name, who deleted them, and when.
                            Filter by type (Timers, Users, Roles), search by name, or filter by date range.
                            Click the Type or Deleted At column headers to sort the table.
                        </p>

                        <h3 class="text-timerbot-teal uppercase tracking-wide" style="font-family: var(--font-display);">Restoring Items</h3>
                        <p>
                            Click <strong>Restore</strong> on any trashed item to bring it back to its original state.
                            Restored items reappear in their respective lists as if they were never deleted.
                        </p>

                        <h3 class="text-timerbot-teal uppercase tracking-wide" style="font-family: var(--font-display);">Permanent Deletion</h3>
                        <p>
                            Click <strong>Delete</strong> on a trashed item to permanently remove it. This action cannot be undone.
                            The <strong>Empty Trash</strong> button permanently deletes all trashed items (with a confirmation prompt).
                            You can filter by type before emptying to only remove items of a specific type.
                        </p>
                    </div>
                </section>
            @endif

            {{-- Activity Log --}}
            @if($canViewLogs)
                <section id="activity-log" class="border-t border-subtle mt-4 pt-2">
                    <h2 class="text-xl uppercase tracking-wider text-timerbot-green mb-4 border-b border-subtle pb-2" style="font-family: var(--font-display);">
                        {{ ++$n }}. Activity Log
                    </h2>
                    <div class="space-y-4 text-sm leading-relaxed">
                        <p>
                            The Activity Log provides a comprehensive audit trail of all actions taken in the application.
                            Navigate to <strong>Utils &rarr; Activity Log</strong> to view it.
                        </p>

                        <h3 class="text-timerbot-teal uppercase tracking-wide" style="font-family: var(--font-display);">What Is Logged</h3>
                        <p>
                            Every create, update, delete, and run action is recorded with the date and time, the user who performed it,
                            the type and name of the affected item, and a summary of what changed.
                        </p>

                        <h3 class="text-timerbot-teal uppercase tracking-wide" style="font-family: var(--font-display);">Filtering &amp; Sorting</h3>
                        <p>
                            Filter the log by:
                        </p>
                        <ul class="list-disc list-inside ml-4 space-y-1">
                            <li><strong>Search</strong> &mdash; Search by model name or user.</li>
                            <li><strong>Type</strong> &mdash; Filter by item type (User, Timer, Role, etc.).</li>
                            <li><strong>Action</strong> &mdash; Filter by action (created, updated, deleted, run).</li>
                            <li><strong>User</strong> &mdash; Filter by the user who performed the action.</li>
                            <li><strong>Date Range</strong> &mdash; Filter by date range.</li>
                        </ul>
                        <p>
                            Click any column header (Date/Time, Action, Type, Model, User) to sort the table.
                            Click again to reverse the sort direction. Filters and sort are preserved together.
                        </p>

                        <h3 class="text-timerbot-teal uppercase tracking-wide" style="font-family: var(--font-display);">Viewing Details</h3>
                        <p>
                            Click <strong>View</strong> on any log entry to see the full change history, showing exactly which fields changed
                            and their before/after values.
                        </p>
                    </div>
                </section>
            @endif

            {{-- Settings --}}
            @if($canManageSettings)
                <section id="settings" class="border-t border-subtle mt-4 pt-2">
                    <h2 class="text-xl uppercase tracking-wider text-timerbot-green mb-4 border-b border-subtle pb-2" style="font-family: var(--font-display);">
                        {{ ++$n }}. Settings
                    </h2>
                    <div class="space-y-4 text-sm leading-relaxed">
                        <p>
                            App-wide settings are managed from <strong>Utils &rarr; Settings</strong> (requires the <em>settings.manage</em> permission).
                        </p>
                        <p>
                            Settings are grouped by category. Types include boolean toggles, numbers, text fields, and rich-text editors.
                            Changes are saved automatically via AJAX &mdash; look for the save indicator in the top-right corner.
                        </p>
                        <p>
                            The <strong>News</strong> setting (rich text) controls the content displayed on the home/welcome page.
                            When news is updated, users who haven't viewed it yet will see an indicator badge in the navigation bar.
                        </p>
                    </div>
                </section>
            @endif

            {{-- Appearance --}}
            <section id="appearance" class="border-t border-subtle mt-4 pt-2">
                <h2 class="text-xl uppercase tracking-wider text-timerbot-green mb-4 border-b border-subtle pb-2" style="font-family: var(--font-display);">
                    {{ ++$n }}. Appearance
                </h2>
                <div class="space-y-4 text-sm leading-relaxed">

                    <h3 class="text-timerbot-teal uppercase tracking-wide" style="font-family: var(--font-display);">Light &amp; Dark Mode</h3>
                    <p>
                        {{ config('app.name', 'Timerbot') }} supports both light and dark themes. Toggle between them using the
                        sun/moon icon in the navigation bar. Your preference is saved in your browser and persists between visits.
                    </p>

                    <h3 class="text-timerbot-teal uppercase tracking-wide" style="font-family: var(--font-display);">Sortable Tables</h3>
                    <p>
                        All table-based pages (Timers, Users, Roles, Trash, Activity Log) feature sortable column headers.
                        Click a column header to sort by that column. Click the same header again to toggle between ascending
                        and descending order. The active sort column is highlighted, and sort preferences are preserved
                        when using filters or navigating between pages.
                    </p>
                </div>
            </section>

            {{-- Keyboard Shortcuts --}}
            @auth
                <section id="keyboard-shortcuts" class="border-t border-subtle mt-4 pt-2">
                    <h2 class="text-xl uppercase tracking-wider text-timerbot-green mb-4 border-b border-subtle pb-2" style="font-family: var(--font-display);">
                        {{ ++$n }}. Keyboard Shortcuts
                    </h2>
                    <div class="space-y-4 text-sm leading-relaxed">
                        <div class="overflow-x-auto">
                            <table class="w-full text-left">
                                <thead>
                                    <tr class="border-b border-subtle">
                                        <th class="py-2 pr-6 uppercase tracking-wider text-timerbot-green text-xs" style="font-family: var(--font-display);">Shortcut</th>
                                        <th class="py-2 uppercase tracking-wider text-timerbot-green text-xs" style="font-family: var(--font-display);">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="border-b border-subtle">
                                        <td class="py-2 pr-6"><kbd class="px-2 py-0.5 border border-subtle text-timerbot-teal text-xs">Ctrl+S</kbd> / <kbd class="px-2 py-0.5 border border-subtle text-timerbot-teal text-xs">Cmd+S</kbd></td>
                                        <td class="py-2">Save the current form (on pages with AJAX auto-save)</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <p>
                            On form pages, a <strong>save indicator</strong> appears in the top-right corner of the screen.
                            It shows the current save status:
                        </p>
                        <ul class="list-disc list-inside ml-4 space-y-1">
                            <li><strong class="text-timerbot-red">Unsaved</strong> &mdash; You have unsaved changes (pulsing red). Click the indicator or press Ctrl+S to save.</li>
                            <li><strong class="text-timerbot-green">Saving</strong> &mdash; Save is in progress.</li>
                            <li><strong class="text-timerbot-green">Saved</strong> &mdash; All changes have been saved.</li>
                        </ul>
                    </div>
                </section>
            @endauth

        </div>

        {{-- Back to top --}}
        <div class="mt-10 pt-4 border-t border-subtle text-center">
            <a href="#" onclick="this.closest('main').scrollTo({top:0,behavior:'smooth'});return false" class="text-timerbot-teal hover:text-timerbot-lime text-sm" style="font-family: var(--font-display);">
                &uarr; Back to Top
            </a>
        </div>

    </div>
</x-layouts.app>
