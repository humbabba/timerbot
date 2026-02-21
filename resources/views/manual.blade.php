<x-layouts.app>
    <div class="max-w-4xl mx-auto px-4 py-8">

        {{-- Title --}}
        <h1 class="text-3xl md:text-4xl mb-2" style="font-family: var(--font-display); text-shadow: 0 0 10px rgba(51,255,51,0.5);">
            User Manual
        </h1>
        <p class="text-text-muted text-sm mb-8" style="font-family: var(--font-display);">
            v{{ config('app.version', '0.00.00.01') }}
        </p>

        {{-- Table of Contents --}}
        <nav class="timerbot-panel p-4 md:p-6 mb-8">
            <h2 class="text-lg uppercase tracking-wider text-timerbot-neon mb-4" style="font-family: var(--font-display);">
                Table of Contents
            </h2>
            <ol class="list-decimal list-inside space-y-1 text-sm">
                <li><a href="#overview" class="text-timerbot-mint hover:text-timerbot-lime">Overview</a></li>
                <li><a href="#getting-started" class="text-timerbot-mint hover:text-timerbot-lime">Getting Started</a></li>
                <li><a href="#timers" class="text-timerbot-mint hover:text-timerbot-lime">Timers</a></li>
                <li><a href="#running-a-timer" class="text-timerbot-mint hover:text-timerbot-lime">Running a Timer</a></li>
                <li><a href="#participant-view" class="text-timerbot-mint hover:text-timerbot-lime">Participant View</a></li>
                <li><a href="#user-management" class="text-timerbot-mint hover:text-timerbot-lime">User Management</a></li>
                <li><a href="#groups" class="text-timerbot-mint hover:text-timerbot-lime">Groups</a></li>
                <li><a href="#trash-recovery" class="text-timerbot-mint hover:text-timerbot-lime">Trash &amp; Recovery</a></li>
                <li><a href="#activity-log" class="text-timerbot-mint hover:text-timerbot-lime">Activity Log</a></li>
                <li><a href="#settings" class="text-timerbot-mint hover:text-timerbot-lime">Settings</a></li>
                <li><a href="#keyboard-shortcuts" class="text-timerbot-mint hover:text-timerbot-lime">Keyboard Shortcuts</a></li>
            </ol>
        </nav>

        {{-- Sections --}}
        <div class="space-y-12">

            {{-- 1. Overview --}}
            <section id="overview" class="pt-4">
                <h2 class="text-xl uppercase tracking-wider text-timerbot-neon mb-4 border-b border-dim-green pb-2" style="font-family: var(--font-display);">
                    1. Overview
                </h2>
                <div class="space-y-3 text-sm leading-relaxed">
                    <p>
                        {{ config('app.name', 'Timerbot') }} is a collaborative meeting timer designed to keep meetings on track.
                        An operator creates a timer by setting a meeting end time and the number of participants, then runs the timer during the meeting.
                        The available time is divided equally among all speakers, and each speaker's countdown is displayed in real time.
                    </p>
                    <p>
                        Participants can view their countdown on a public display page &mdash; no login required for public timers.
                        The operator controls the flow: starting the timer, advancing to the next speaker, pausing, and stopping.
                    </p>
                    <p>
                        Additional features include configurable audio warnings, group-based access control, role-based permissions,
                        activity logging, soft-delete with trash and restore, and app-wide settings.
                    </p>
                </div>
            </section>

            {{-- 2. Getting Started --}}
            <section id="getting-started" class="border-t border-dim-green mt-4 pt-2">
                <h2 class="text-xl uppercase tracking-wider text-timerbot-neon mb-4 border-b border-dim-green pb-2" style="font-family: var(--font-display);">
                    2. Getting Started
                </h2>
                <div class="space-y-4 text-sm leading-relaxed">
                    <h3 class="text-timerbot-mint uppercase tracking-wide" style="font-family: var(--font-display);">Registration</h3>
                    <p>
                        Navigate to the <strong>Register</strong> page and enter your name and email address.
                        After registering you will be logged in automatically.
                        Your avatar is pulled from <a href="https://gravatar.com" target="_blank" class="text-timerbot-mint hover:text-timerbot-lime">Gravatar</a> based on your email.
                    </p>

                    <h3 class="text-timerbot-mint uppercase tracking-wide" style="font-family: var(--font-display);">Logging In</h3>
                    <p>
                        {{ config('app.name', 'Timerbot') }} uses <strong>magic link</strong> authentication &mdash; no passwords.
                        Enter your email on the Login page and a one-time login link will be sent to your inbox.
                        Click the link to sign in.
                    </p>

                    <h3 class="text-timerbot-mint uppercase tracking-wide" style="font-family: var(--font-display);">Navigation</h3>
                    <p>
                        The top navigation bar provides access to all areas of the app:
                    </p>
                    <ul class="list-disc list-inside ml-4 space-y-1">
                        <li><strong>Home</strong> &mdash; The welcome page displaying news and announcements.</li>
                        <li><strong>Timers</strong> &mdash; View, create, and manage timers.</li>
                        <li><strong>Utils</strong> &mdash; A dropdown with links to Users, Roles, Trash, Settings, and Activity Log (visible based on your permissions).</li>
                        <li><strong>Profile</strong> &mdash; Your avatar and name appear on the right; click to view your profile or log out.</li>
                    </ul>
                    <p>
                        On mobile devices the navigation collapses into a hamburger menu.
                    </p>
                </div>
            </section>

            {{-- 3. Timers --}}
            <section id="timers" class="border-t border-dim-green mt-4 pt-2">
                <h2 class="text-xl uppercase tracking-wider text-timerbot-neon mb-4 border-b border-dim-green pb-2" style="font-family: var(--font-display);">
                    3. Timers
                </h2>
                <div class="space-y-4 text-sm leading-relaxed">

                    <h3 class="text-timerbot-mint uppercase tracking-wide" style="font-family: var(--font-display);">Timer List</h3>
                    <p>
                        The Timers page shows all timers you have access to. Use the search box and date-range filters to find specific timers.
                        App admins can toggle between <strong>My Timers</strong> and <strong>All Timers</strong>.
                    </p>

                    <h3 class="text-timerbot-mint uppercase tracking-wide" style="font-family: var(--font-display);">Creating a Timer</h3>
                    <p>
                        Click <strong>Add Timer</strong> to create a new timer. Fill in the following fields:
                    </p>
                    <ul class="list-disc list-inside ml-4 space-y-1">
                        <li><strong>Name</strong> &mdash; A descriptive name for the timer (required).</li>
                        <li><strong>Visibility</strong> &mdash; <em>Public</em> (anyone with the link can view) or <em>Private</em> (only group members and app admins).</li>
                        <li><strong>Group</strong> &mdash; Create a new group or select an existing one. Groups control who can run and manage the timer.</li>
                        <li><strong>End Time</strong> &mdash; The time the meeting should end (HH:MM format).</li>
                        <li><strong>Participants</strong> &mdash; The number of speakers (1&ndash;999).</li>
                        <li><strong>Warnings</strong> &mdash; Audio alerts that fire at configurable offsets relative to each speaker's time running out. Choose from sounds like Alarm, Bell, Beep, Chime, Ding, Twang, and Warning. Use the preview button to hear each sound.</li>
                        <li><strong>Participant Message</strong> &mdash; Optional HTML-supported message displayed to participants on the public view page.</li>
                    </ul>

                    <h3 class="text-timerbot-mint uppercase tracking-wide" style="font-family: var(--font-display);">Warning Countdown Values</h3>
                    <p>
                        Each warning has a <strong>countdown value</strong> in seconds. A positive value (e.g. 60) means the warning fires
                        60 seconds <em>before</em> the speaker's time expires. A value of 0 fires exactly when time runs out.
                        A negative value (e.g. &minus;30) fires 30 seconds <em>after</em> the speaker's time has expired.
                        Values range from &minus;3600 to 3600.
                    </p>

                    <h3 class="text-timerbot-mint uppercase tracking-wide" style="font-family: var(--font-display);">Editing &amp; Copying</h3>
                    <p>
                        Group admins and app admins can edit a timer's settings at any time. Use the <strong>Copy</strong> action
                        to duplicate a timer along with its settings &mdash; a new group will be created for the copy.
                    </p>

                    <h3 class="text-timerbot-mint uppercase tracking-wide" style="font-family: var(--font-display);">Deleting Timers</h3>
                    <p>
                        Deleting a timer moves it to the <a href="#trash-recovery" class="text-timerbot-mint hover:text-timerbot-lime">Trash</a>.
                        It can be restored from there, or permanently deleted.
                    </p>
                </div>
            </section>

            {{-- 4. Running a Timer --}}
            <section id="running-a-timer" class="border-t border-dim-green mt-4 pt-2">
                <h2 class="text-xl uppercase tracking-wider text-timerbot-neon mb-4 border-b border-dim-green pb-2" style="font-family: var(--font-display);">
                    4. Running a Timer
                </h2>
                <div class="space-y-4 text-sm leading-relaxed">

                    <h3 class="text-timerbot-mint uppercase tracking-wide" style="font-family: var(--font-display);">Operator Controls</h3>
                    <p>
                        Click <strong>Run Timer</strong> from the timer list or the participant view page.
                        The run page is the operator's control panel.
                    </p>
                    <ul class="list-disc list-inside ml-4 space-y-1">
                        <li><strong>Start</strong> &mdash; Begins the timer and starts Speaker 1's countdown.</li>
                        <li><strong>Next Speaker</strong> &mdash; Records the current speaker's time and advances to the next speaker. Remaining meeting time is redistributed equally among the remaining speakers.</li>
                        <li><strong>Pause / Resume</strong> &mdash; Freezes the current speaker's countdown. While paused, time does not count against the speaker. Pause duration is tracked and accounted for.</li>
                        <li><strong>Stop</strong> &mdash; Ends the timer session and resets to idle state.</li>
                    </ul>

                    <h3 class="text-timerbot-mint uppercase tracking-wide" style="font-family: var(--font-display);">Live Settings</h3>
                    <p>
                        While running, you can adjust settings on the fly:
                    </p>
                    <ul class="list-disc list-inside ml-4 space-y-1">
                        <li><strong>End Time</strong> &mdash; Shift the meeting end time. The per-speaker allocation recalculates instantly.</li>
                        <li><strong>Participant Count</strong> &mdash; Add or reduce speakers (cannot reduce below current speaker + 1). Time is redistributed automatically.</li>
                    </ul>

                    <h3 class="text-timerbot-mint uppercase tracking-wide" style="font-family: var(--font-display);">Meeting Countdown</h3>
                    <p>
                        The top of the run page shows a countdown to the meeting end time and the calculated time per person.
                        If the meeting end time is in the past, the app assumes the next occurrence (i.e. the following day).
                    </p>

                    <h3 class="text-timerbot-mint uppercase tracking-wide" style="font-family: var(--font-display);">Speaker Panel</h3>
                    <p>
                        The central panel shows the current speaker number (e.g. "Speaker 3 of 10") and a large countdown timer.
                        The timer is green when the speaker is on time, and turns red with a pulsing animation when the speaker goes over.
                    </p>

                    <h3 class="text-timerbot-mint uppercase tracking-wide" style="font-family: var(--font-display);">Speaker History</h3>
                    <p>
                        After the first speaker finishes, a history table appears showing each completed speaker's allotted time,
                        actual time used, and whether they were on time or over. Over-time speakers are highlighted.
                    </p>

                    <h3 class="text-timerbot-mint uppercase tracking-wide" style="font-family: var(--font-display);">Audio Warnings</h3>
                    <p>
                        Configured warnings fire automatically at the specified countdown values during each speaker's turn.
                        Sounds are generated using the Web Audio API &mdash; no external audio files are needed.
                        Make sure your browser's sound is not muted.
                    </p>

                    <h3 class="text-timerbot-mint uppercase tracking-wide" style="font-family: var(--font-display);">State Persistence</h3>
                    <p>
                        Timer state is saved to the server every 3 seconds. If you refresh the page or lose your connection,
                        the timer will resume from its last saved state.
                    </p>
                </div>
            </section>

            {{-- 5. Participant View --}}
            <section id="participant-view" class="border-t border-dim-green mt-4 pt-2">
                <h2 class="text-xl uppercase tracking-wider text-timerbot-neon mb-4 border-b border-dim-green pb-2" style="font-family: var(--font-display);">
                    5. Participant View
                </h2>
                <div class="space-y-4 text-sm leading-relaxed">

                    <h3 class="text-timerbot-mint uppercase tracking-wide" style="font-family: var(--font-display);">What Participants See</h3>
                    <p>
                        The participant view is a read-only display that updates in real time. It shows:
                    </p>
                    <ul class="list-disc list-inside ml-4 space-y-1">
                        <li>Timer name and running status (running, paused, ended, or not running).</li>
                        <li>Stats bar: total participants, current time, end time, and meeting time remaining.</li>
                        <li>Time allotted per participant (recalculates live as speakers complete).</li>
                        <li>Current speaker number and their countdown timer.</li>
                        <li>The optional participant message.</li>
                    </ul>
                    <p>
                        If the current speaker goes over time, the per-participant time display flashes red to indicate
                        that remaining speakers' allocations are decreasing.
                    </p>

                    <h3 class="text-timerbot-mint uppercase tracking-wide" style="font-family: var(--font-display);">Sharing the Timer URL</h3>
                    <p>
                        Share the timer's URL with participants. For <strong>public</strong> timers, anyone with the link
                        can view the countdown &mdash; no login required. For <strong>private</strong> timers, viewers must
                        be logged-in group members or app admins.
                    </p>

                    <h3 class="text-timerbot-mint uppercase tracking-wide" style="font-family: var(--font-display);">Keep Screen Awake</h3>
                    <p>
                        The participant view includes a <strong>"Keep screen awake"</strong> checkbox.
                        Enabling this uses the browser's Wake Lock API to prevent the screen from dimming or locking
                        while the timer is displayed &mdash; useful when projecting the timer on a shared screen.
                    </p>

                    <h3 class="text-timerbot-mint uppercase tracking-wide" style="font-family: var(--font-display);">Real-Time Updates</h3>
                    <p>
                        The participant view polls the server every 2 seconds for state changes and renders countdowns
                        every 200ms for a smooth display. All time calculations are performed client-side for accuracy.
                    </p>
                </div>
            </section>

            {{-- 6. User Management --}}
            <section id="user-management" class="border-t border-dim-green mt-4 pt-2">
                <h2 class="text-xl uppercase tracking-wider text-timerbot-neon mb-4 border-b border-dim-green pb-2" style="font-family: var(--font-display);">
                    6. User Management
                </h2>
                <div class="space-y-4 text-sm leading-relaxed">
                    <p>
                        Users with the appropriate permissions can manage user accounts from <strong>Utils &rarr; Users</strong>.
                    </p>

                    <h3 class="text-timerbot-mint uppercase tracking-wide" style="font-family: var(--font-display);">User List</h3>
                    <p>
                        The user list shows all registered users with their name, email, role(s), registration date, and last login.
                        Use search and date-range filters to find specific users.
                    </p>

                    <h3 class="text-timerbot-mint uppercase tracking-wide" style="font-family: var(--font-display);">Editing Users</h3>
                    <p>
                        Edit a user's name, email, and assigned role. You can only edit users whose roles you have permission to assign.
                        Each user can view and edit their own profile, including setting a preferred <strong>Starting View</strong>
                        (the page that loads after login).
                    </p>

                    <h3 class="text-timerbot-mint uppercase tracking-wide" style="font-family: var(--font-display);">Roles &amp; Permissions</h3>
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
                </div>
            </section>

            {{-- 7. Groups --}}
            <section id="groups" class="border-t border-dim-green mt-4 pt-2">
                <h2 class="text-xl uppercase tracking-wider text-timerbot-neon mb-4 border-b border-dim-green pb-2" style="font-family: var(--font-display);">
                    7. Groups
                </h2>
                <div class="space-y-4 text-sm leading-relaxed">
                    <p>
                        Groups control who can access and manage timers. Every timer belongs to exactly one group.
                    </p>

                    <h3 class="text-timerbot-mint uppercase tracking-wide" style="font-family: var(--font-display);">How Groups Work</h3>
                    <ul class="list-disc list-inside ml-4 space-y-1">
                        <li>When you create a timer, you either create a new group or select an existing one.</li>
                        <li>The timer creator is automatically added as a <strong>group admin</strong>.</li>
                        <li>You can add other users as members by searching for them by name or email.</li>
                        <li>Groups can be reused across multiple timers.</li>
                    </ul>

                    <h3 class="text-timerbot-mint uppercase tracking-wide" style="font-family: var(--font-display);">Admin vs. Member</h3>
                    <ul class="list-disc list-inside ml-4 space-y-1">
                        <li><strong>Group Members</strong> can run the timer and view private timers belonging to the group.</li>
                        <li><strong>Group Admins</strong> can do everything members can, plus edit and delete timers in the group and manage group membership.</li>
                    </ul>
                    <p>
                        App admins always have full access to all timers regardless of group membership.
                    </p>
                </div>
            </section>

            {{-- 8. Trash & Recovery --}}
            <section id="trash-recovery" class="border-t border-dim-green mt-4 pt-2">
                <h2 class="text-xl uppercase tracking-wider text-timerbot-neon mb-4 border-b border-dim-green pb-2" style="font-family: var(--font-display);">
                    8. Trash &amp; Recovery
                </h2>
                <div class="space-y-4 text-sm leading-relaxed">
                    <p>
                        When you delete a timer, user, or role, it is <strong>soft-deleted</strong> &mdash; moved to the Trash rather than permanently destroyed.
                        Navigate to <strong>Utils &rarr; Trash</strong> to manage deleted items.
                    </p>

                    <h3 class="text-timerbot-mint uppercase tracking-wide" style="font-family: var(--font-display);">Viewing Trash</h3>
                    <p>
                        The trash list shows all soft-deleted items with their type, name, who deleted them, and when.
                        Filter by type (Timers, Users, Roles), search by name, or filter by date range.
                    </p>

                    <h3 class="text-timerbot-mint uppercase tracking-wide" style="font-family: var(--font-display);">Restoring Items</h3>
                    <p>
                        Click <strong>Restore</strong> on any trashed item to bring it back to its original state.
                        Restored items reappear in their respective lists as if they were never deleted.
                    </p>

                    <h3 class="text-timerbot-mint uppercase tracking-wide" style="font-family: var(--font-display);">Permanent Deletion</h3>
                    <p>
                        Click <strong>Delete</strong> on a trashed item to permanently remove it. This action cannot be undone.
                        The <strong>Empty Trash</strong> button permanently deletes all trashed items (with a confirmation prompt).
                        You can filter by type before emptying to only remove items of a specific type.
                    </p>
                </div>
            </section>

            {{-- 9. Activity Log --}}
            <section id="activity-log" class="border-t border-dim-green mt-4 pt-2">
                <h2 class="text-xl uppercase tracking-wider text-timerbot-neon mb-4 border-b border-dim-green pb-2" style="font-family: var(--font-display);">
                    9. Activity Log
                </h2>
                <div class="space-y-4 text-sm leading-relaxed">
                    <p>
                        The Activity Log provides a comprehensive audit trail of all actions taken in the application.
                        Navigate to <strong>Utils &rarr; Activity Log</strong> to view it.
                    </p>

                    <h3 class="text-timerbot-mint uppercase tracking-wide" style="font-family: var(--font-display);">What Is Logged</h3>
                    <p>
                        Every create, update, delete, and run action is recorded with the date and time, the user who performed it,
                        the type and name of the affected item, and a summary of what changed.
                    </p>

                    <h3 class="text-timerbot-mint uppercase tracking-wide" style="font-family: var(--font-display);">Filtering</h3>
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

                    <h3 class="text-timerbot-mint uppercase tracking-wide" style="font-family: var(--font-display);">Viewing Details</h3>
                    <p>
                        Click on any log entry to view the full change history, showing exactly which fields changed
                        and their before/after values.
                    </p>
                </div>
            </section>

            {{-- 10. Settings --}}
            <section id="settings" class="border-t border-dim-green mt-4 pt-2">
                <h2 class="text-xl uppercase tracking-wider text-timerbot-neon mb-4 border-b border-dim-green pb-2" style="font-family: var(--font-display);">
                    10. Settings
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

            {{-- 11. Keyboard Shortcuts --}}
            <section id="keyboard-shortcuts" class="border-t border-dim-green mt-4 pt-2">
                <h2 class="text-xl uppercase tracking-wider text-timerbot-neon mb-4 border-b border-dim-green pb-2" style="font-family: var(--font-display);">
                    11. Keyboard Shortcuts
                </h2>
                <div class="space-y-4 text-sm leading-relaxed">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="border-b border-dim-green">
                                    <th class="py-2 pr-6 uppercase tracking-wider text-timerbot-neon text-xs" style="font-family: var(--font-display);">Shortcut</th>
                                    <th class="py-2 uppercase tracking-wider text-timerbot-neon text-xs" style="font-family: var(--font-display);">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="border-b border-dim-green">
                                    <td class="py-2 pr-6"><kbd class="px-2 py-0.5 border border-dim-green text-timerbot-mint text-xs">Ctrl+S</kbd> / <kbd class="px-2 py-0.5 border border-dim-green text-timerbot-mint text-xs">Cmd+S</kbd></td>
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
                        <li><strong class="text-timerbot-neon">Saving</strong> &mdash; Save is in progress.</li>
                        <li><strong class="text-timerbot-green">Saved</strong> &mdash; All changes have been saved.</li>
                    </ul>
                </div>
            </section>

        </div>

        {{-- Back to top --}}
        <div class="mt-10 pt-4 border-t border-dim-green text-center">
            <a href="#" class="text-timerbot-mint hover:text-timerbot-lime text-sm" style="font-family: var(--font-display);">
                &uarr; Back to Top
            </a>
        </div>

    </div>
</x-layouts.app>
