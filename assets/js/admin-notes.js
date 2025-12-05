/**
 * Admin Notes JavaScript
 *
 * @package Partner_CIU_Manager
 */

(function($) {
    'use strict';

    var adminNotes = {
        /**
         * Initialize
         */
        init: function() {
            this.bindEvents();
            this.updateHiddenField();
        },

        /**
         * Bind events
         */
        bindEvents: function() {
            var self = this;

            // Add new note
            $('#save-note-btn').on('click', function(e) {
                e.preventDefault();
                self.addNote();
            });

            // Delete note
            $(document).on('click', '.delete-note-btn', function(e) {
                e.preventDefault();
                var noteId = $(this).data('note-id');
                self.deleteNote(noteId);
            });

            // Edit note
            $(document).on('click', '.edit-note-btn', function(e) {
                e.preventDefault();
                var noteId = $(this).data('note-id');
                self.editNote(noteId);
            });
        },

        /**
         * Add new note
         */
        addNote: function() {
            var noteName = $('#note-name').val().trim();
            var noteContent = $('#note-content').val().trim();

            if (!noteName || !noteContent) {
                alert('Please fill in both note name and content.');
                return;
            }

            // Get current notes
            var notes = this.getNotesData();

            // Create new note
            var now = new Date();
            var dateString = now.getFullYear() + '-' +
                           String(now.getMonth() + 1).padStart(2, '0') + '-' +
                           String(now.getDate()).padStart(2, '0') + ' ' +
                           String(now.getHours()).padStart(2, '0') + ':' +
                           String(now.getMinutes()).padStart(2, '0') + ':' +
                           String(now.getSeconds()).padStart(2, '0');

            var newNote = {
                note_id: Date.now(),
                note_name: noteName,
                note_content: noteContent,
                created_date: dateString,
                last_modified: dateString
            };

            // Add to notes array
            notes.push(newNote);

            // Update hidden field
            this.setNotesData(notes);

            // Add to UI
            this.addNoteToList(newNote);

            // Clear form
            $('#note-name').val('');
            $('#note-content').val('');

            // Remove "no notes" message if exists
            $('.no-notes-message').remove();
        },

        /**
         * Delete note
         */
        deleteNote: function(noteId) {
            if (!confirm(partnerAdminNotesData.strings.confirmRemove)) {
                return;
            }

            // Get current notes
            var notes = this.getNotesData();

            // Remove note
            notes = notes.filter(function(note) {
                return note.note_id != noteId;
            });

            // Update hidden field
            this.setNotesData(notes);

            // Remove from UI
            $('.note-item[data-note-id="' + noteId + '"]').fadeOut(300, function() {
                $(this).remove();

                // Show "no notes" message if list is empty
                if ($('.note-item').length === 0) {
                    $('.notes-list').html('<p class="no-notes-message">No notes yet. Add your first note below.</p>');
                }
            });
        },

        /**
         * Edit note
         */
        editNote: function(noteId) {
            // Get note data
            var notes = this.getNotesData();
            var note = notes.find(function(n) {
                return n.note_id == noteId;
            });

            if (!note) {
                return;
            }

            // Populate form
            $('#note-name').val(note.note_name);
            $('#note-content').val(note.note_content);

            // Delete the note (will be re-added when saved)
            this.deleteNote(noteId);

            // Scroll to form
            $('html, body').animate({
                scrollTop: $('.add-note-section').offset().top - 50
            }, 500);

            // Focus on note name
            $('#note-name').focus();
        },

        /**
         * Add note to UI list
         */
        addNoteToList: function(note) {
            var noteHTML = '<div class="note-item new-note" data-note-id="' + note.note_id + '">' +
                '<div class="note-header">' +
                    '<strong class="note-name">' + this.escapeHtml(note.note_name) + '</strong>' +
                    '<span class="note-meta">Just now</span>' +
                    '<button type="button" class="button button-small edit-note-btn" data-note-id="' + note.note_id + '">' +
                        partnerAdminNotesData.strings.edit +
                    '</button>' +
                    '<button type="button" class="button button-small button-link-delete delete-note-btn" data-note-id="' + note.note_id + '">' +
                        partnerAdminNotesData.strings.remove +
                    '</button>' +
                '</div>' +
                '<div class="note-content">' +
                    '<p>' + this.escapeHtml(note.note_content).replace(/\n/g, '<br>') + '</p>' +
                '</div>' +
            '</div>';

            $('#notes-list').append(noteHTML);

            // Scroll to new note
            var $newNote = $('.note-item[data-note-id="' + note.note_id + '"]');
            $('html, body').animate({
                scrollTop: $newNote.offset().top - 100
            }, 500);
        },

        /**
         * Get notes data from hidden field
         */
        getNotesData: function() {
            var data = $('#partner-admin-notes-data').val();
            try {
                return JSON.parse(data) || [];
            } catch (e) {
                return [];
            }
        },

        /**
         * Set notes data to hidden field
         */
        setNotesData: function(notes) {
            $('#partner-admin-notes-data').val(JSON.stringify(notes));
        },

        /**
         * Update hidden field with current notes
         */
        updateHiddenField: function() {
            // This ensures the hidden field is updated with any changes
            var notes = [];
            $('.note-item').each(function() {
                var $item = $(this);
                notes.push({
                    note_id: $item.data('note-id'),
                    note_name: $item.find('.note-name').text(),
                    note_content: $item.find('.note-content p').text()
                });
            });
            this.setNotesData(notes);
        },

        /**
         * Escape HTML to prevent XSS
         */
        escapeHtml: function(text) {
            var map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text.replace(/[&<>"']/g, function(m) { return map[m]; });
        }
    };

    // Initialize on document ready
    $(document).ready(function() {
        adminNotes.init();
    });

})(jQuery);
