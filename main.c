#include <stdio.h>
#include <ncurses.h>
#include <malloc.h>
#include <time.h>
#include <string.h>
#include <stdlib.h>
#include <ctype.h>

// select upcoming task
// select next task
// select previous task
//
// start selected task n minutes earlier
// start selected task n minutes later 
// end selected task n minutes earlier 
// end selected task n minutes later 
//
// shift selected task 5 minutes earlier
// shift selected task 5 minutes later
//
// expand selected task to current time (either direction)
// expand selected task task up to previous boundary
// expand selected task down up to next boundary
//
// shift selected task up to previous
// shift selected task down to next
//
// swap selected task with previous
// swap selected task with next
//
// delete selected task
// add new task at soonest unallocated time
//
// shift modes:
//  * fill gaps
//  * shift gaps, too
//
// task properties:
//  * description
//  * color
//  * all day, all week, all month
//  * visible on day, week, month view
//  * children tasks?
//  * notes
//  * checklist item
//  * begin day, end day
//  * begin time, end time
//  * rigidity
//  * immovable
//

// row: fg color
// col: bg color
int good_colors[][8] = {
    // 0, 1, 2, 3, 4, 5, 6, 7
    { 1, 1, 1, 1, 0, 1, 1, 1 }, // 0
    { 1, 0, 1, 1, 1, 0, 1, 1 }, // 1
    { 1, 1, 0, 0, 1, 1, 0, 1 }, // 2
    { 1, 1, 0, 0, 1, 1, 0, 0 }, // 3
    { 1, 1, 1, 1, 0, 1, 1, 1 }, // 4
    { 1, 0, 1, 1, 1, 0, 1, 1 }, // 5
    { 1, 1, 0, 0, 1, 1, 0, 0 }, // 6
    { 1, 1, 1, 0, 1, 1, 1, 0 }, // 7
};

typedef enum month_t month_t;
enum month_t {JANUARY, FEBRUARY, MARCH, APRIL, MAY, JUNE, JULY, AUGUST, SEPTEMBER, OCTOBER, NOVEMBER, DECEMBER};

typedef struct color_t {
    int red, green, blue;
} color_t;

typedef struct calendar_t calendar_t;

typedef struct task_t {
    calendar_t * calendar;
    int fg_color;
    int bg_color;
    char * description;
    struct tm start;
    struct tm end;
    int active; // flag for deleting tasks
} task_t;

typedef struct task_node_t task_node_t;

typedef struct task_node_t {
    task_node_t * prev, * next;
    task_t * task;
} task_node_t;

// TODO: use doubly linked list for tasks so that
//       we can access next and previous tasks
typedef struct calendar_t {
    task_node_t * head_task;
    task_t ** tasks;
    size_t num_tasks;
    size_t tasks_cap;
    bool sorted;
} calendar_t;

typedef struct calendar_view_t {
    calendar_t * calendar;
    struct tm top;
    int granularity;
    task_node_t * selected_task_node;
    int hold_start;
    int hold_end;
} calendar_view_t;

void initCalendar(calendar_t * c) {
    size_t i, num, cap;

    num = c->num_tasks = 0;
    cap = c->tasks_cap = 10;
    c->tasks = malloc(sizeof(*c->tasks) * cap);

    c->head_task = NULL;
    for(i=0; i < cap; i++) {
        c->tasks[i] = NULL;
    }
    c->sorted = false;
}

void saveToFile(calendar_view_t * cv) {

    FILE * fp = fopen("save.data", "w");
    size_t i = 0;

    if(fp == NULL) {
        return;
    }

    task_node_t * task_node = cv->calendar->head_task;
    while(task_node != NULL) {
        time_t start = mktime(&task_node->task->start);
        time_t end = mktime(&task_node->task->end);

        fprintf(fp, "task %d:\n", i);
        fprintf(fp, "description: %s\n", task_node->task->description);
        fprintf(fp, "start: %ld\n", start);
        fprintf(fp, "end: %ld\n", end);
        fprintf(fp, "fg: %d\n", task_node->task->fg_color);
        fprintf(fp, "bg: %d\n", task_node->task->bg_color);
        fprintf(fp, "\n");

        task_node = task_node->next;
        i++;
    }

}

task_t * allocTask(calendar_t * c);
void addTask(calendar_t * c, task_t * t);
void printCalendar(calendar_t * c);

void loadFromFile(calendar_view_t * cv) {
    FILE * fp = fopen("save.data", "r");
    calendar_t * c = cv->calendar;
    char buffer[512];
    int reading_task = 0;

    char * desc;
    time_t start, end;
    int fg, bg;

    if(fp == NULL) {
        return;
    }

    while( fgets(buffer, 512, fp) != NULL) {
        //fprintf(stdout, "%s", buffer);
        if(!reading_task && (strncmp(buffer, "task ", 5) == 0)) {
            reading_task = 1;
            continue;
        }
        if(reading_task && (strncmp(buffer, "\n", 1) == 0)) { // blank line


            task_t * newTask = allocTask(c);
            newTask->active = 1;
            newTask->start.tm_year  = 118;
            newTask->start.tm_mon   =   1;
            newTask->start.tm_wday  =   3;
            newTask->start.tm_mday  =  21;
            newTask->start.tm_yday  =  51;
            newTask->start.tm_isdst =   0;
            newTask->end.tm_year    = 118;
            newTask->end.tm_mon     =   1;
            newTask->end.tm_wday    =   3;
            newTask->end.tm_mday    =  21;
            newTask->end.tm_yday    =  51;
            newTask->end.tm_isdst   =   0;

            newTask->description = desc;

            struct tm * start_tm = localtime(&start);
            newTask->start.tm_hour  =  start_tm->tm_hour;
            newTask->start.tm_min   =  start_tm->tm_min;
            newTask->start.tm_sec   =   0;

            struct tm * end_tm = localtime(&end);
            newTask->end.tm_hour    =  end_tm->tm_hour;
            newTask->end.tm_min     =  end_tm->tm_min;
            newTask->end.tm_sec     =   0;

            newTask->fg_color       = fg;
            newTask->bg_color       = bg;

            //fprintf(stdout, "description: %s\t", desc);
            //fprintf(stdout, "start: %d\t", start);
            //fprintf(stdout, "end: %d\t", end);
            //fprintf(stdout, "fg: %d\t", fg);
            //fprintf(stdout, "bg: %d\n", bg );

            addTask(c, newTask);

            reading_task = 0;
            continue;
        }

        if(strncmp(buffer, "description: ", 13) == 0) {
            desc = strdup(buffer+13);
            size_t l = strlen(desc);
            desc[l-1] = '\0';
        }
        if(strncmp(buffer, "start: ", 7) == 0) {
            start = atoi(buffer+7);
        }
        if(strncmp(buffer, "end: ", 5) == 0) {
            end = atoi(buffer+5);
        }
        if(strncmp(buffer, "fg: ", 4) == 0) {
            fg = atoi(buffer+4);
        }
        if(strncmp(buffer, "bg: ", 4) == 0) {
            bg = atoi(buffer+4);
        }
    }
    printCalendar(c);
    printf("===\n\n\n");
}

task_t * allocTask(calendar_t * c) {
    task_t ** newTask = NULL;

    c->num_tasks++;
    if(c->num_tasks >= c->tasks_cap) {
        size_t prev_cap = c->tasks_cap;
        size_t i;
        c->tasks_cap *= 2;
        c->tasks = realloc(c->tasks, sizeof(*c->tasks) * c->tasks_cap);
        for(i=prev_cap; i < c->tasks_cap; i++) {
            c->tasks[i] = NULL;
        }
    }

    newTask = &(c->tasks[c->num_tasks - 1]);
    *newTask = malloc(sizeof(task_t));
    (*newTask)->calendar = c;

    return *newTask;
}

void addTask(calendar_t * c, task_t * t) {

    if(c->head_task == NULL) {
        c->head_task = malloc(sizeof(*c->head_task));
        c->head_task->task = t;
        c->head_task->next = NULL;
        c->head_task->prev = NULL;
        return;
    }

    // traverse from head node to find insertion point

    time_t t_time = mktime(&t->start);

    int found = 0, add_to_end = 0;
    task_node_t * node = c->head_task;

    while(!found) {
        time_t node_time = mktime(&node->task->start);
        double dt = difftime(node_time, t_time);
        if(dt > 0) {
            found = 1;
            break;
        }
        if(node->next == NULL) {
            found = 1;
            add_to_end = 1;
            break;
        }
        node = node->next;
    }

    task_node_t * new_node = malloc(sizeof(*new_node));
    new_node->task = t;

    if(add_to_end) {      // add to end
        node->next = new_node;
        new_node->prev = node;
        new_node->next = NULL;
    }
    else {
        task_node_t * prev = node->prev;
        if(prev == NULL) {  // insert at head
            new_node->prev = NULL;
            c->head_task = new_node;
        }
        else {              // insert elsewhere
            prev->next = new_node;
            new_node->prev = prev;
        }
        new_node->next = node;
        node->prev = new_node;
    }

}

task_t * selectFirst(calendar_view_t * cv) {
    // TODO: if calendar tasks are sorted, do a binary search
    //    size_t i;
    //    task_t * first_task = cv->calendar->tasks[0];
    //    time_t first_time = mktime(&first_task->start);
    //    for(i=1; i < cv->calendar->num_tasks; i++) {
    //        task_t * task = cv->calendar->tasks[i];
    //        // check to see if task starts today
    //        time_t task_time = mktime(&task->start);
    //        double dt = difftime(task_time, first_time);
    //        if(dt < 0) {
    //            first_task = task;
    //            first_time = task_time;
    //        }
    //    }
    //    cv->selected_task_node = NULL; // TODO
    // TODO: check for null pointers
    task_t * task = NULL;
    cv->selected_task_node = cv->calendar->head_task;
    if(cv->selected_task_node != NULL)
        task = cv->selected_task_node->task;
    return task;
}

task_t * selectUpcoming(calendar_view_t * cv) {
    return NULL;
}

task_t * selectNext(calendar_view_t * cv) {
    if(cv->selected_task_node->next != NULL) {
        cv->selected_task_node = cv->selected_task_node->next;
    }
    return cv->selected_task_node->task;
}

task_t * selectPrev(calendar_view_t * cv) {
    if(cv->selected_task_node->prev != NULL) {
        cv->selected_task_node = cv->selected_task_node->prev;
    }
    return cv->selected_task_node->task;
}

int last_row_clicked;  // TODO: remove
int first_row_clicked; // TODO: remove

task_t * selectByRowCol(calendar_view_t * cv, int row, int col) {
    calendar_t * calendar = cv->calendar;
    int rows, cols;
    getmaxyx(stdscr, rows, cols);
    int top_hour = cv->top.tm_hour;
    int top_min = cv->top.tm_min;
    int top_row = (top_hour*60 + top_min)/5;

    int found = 0;
    task_node_t * node = calendar->head_task;
    while(!found) {
        int start_hour = node->task->start.tm_hour;
        int start_min  = node->task->start.tm_min;
        int end_hour   = node->task->end.tm_hour;
        int end_min    = node->task->end.tm_min;
        int start_row  = (start_hour*60+start_min)/5 - top_row;
        int end_row    = (end_hour*60+end_min)/5 - top_row - 1; // TODO: deal with rounding
        int width      = 30;

        first_row_clicked = last_row_clicked = 0;   // TODO: this is hideous
        if(row == start_row) first_row_clicked = 1; // TODO: this is hideous
        if(row == end_row) last_row_clicked = 1;    // TODO: this is hideous

        if((row >= start_row) && (row <= end_row) && (col >= 10) && (col <= 40)) {
            found = 1;
            cv->selected_task_node = node;
            break;
        }
        if(node->next == NULL) {
            break;
        }
        node = node->next;
    }

    return cv->selected_task_node->task;
}

void adjustStart(calendar_view_t * cv, int minutes) {
    cv->selected_task_node->task->start.tm_min += minutes;
}

void adjustEnd(calendar_view_t * cv, int minutes) {
    cv->selected_task_node->task->end.tm_min += minutes;
}

// TODO: write a version where you can specify via arguments if you 
//       want the start and end to be held fixed, instead of using state
void shiftBy(calendar_view_t * cv, int minutes) {
    // TODO: this is broken. Need to adjust hours, etc., not just minutes!
    if(!cv->hold_start) {
        cv->selected_task_node->task->start.tm_min += minutes;
    }
    if(!cv->hold_end) {
        cv->selected_task_node->task->end.tm_min += minutes;
    }
    // TODO: add midnight
    task_node_t * prev = cv->selected_task_node->prev;
    task_node_t * next = cv->selected_task_node->next;
    time_t prev_time = (prev != NULL) ? mktime(&prev->task->end) : 0;
    time_t next_time = (next != NULL) ? mktime(&next->task->start) : 0;
    time_t this_start = mktime(&cv->selected_task_node->task->start);
    time_t this_end   = mktime(&cv->selected_task_node->task->end);
    double dt_prev = (prev_time == 0) ? 0 : difftime(this_start, prev_time);
    double dt_next = (next_time == 0) ? 0 : difftime(next_time, this_end);
    int success = ((dt_prev >= 0) && (dt_next >= 0)) ? 1 : 0;

    if(!success) { // undo
        if(!cv->hold_start) {
            cv->selected_task_node->task->start.tm_min -= minutes;
        }
        if(!cv->hold_end) {
            cv->selected_task_node->task->end.tm_min -= minutes;
        }
    }
}

void adjustToTime() {}

void expandUp(calendar_view_t * cv) {
}

void expandDown(calendar_view_t * cv) {
}

void shiftUpToPrev(calendar_view_t * cv) {
}

void shiftDownToNext(calendar_view_t * cv) {
}

void swapWithPrev(calendar_view_t * cv) {
}

void swapWithNext(calendar_view_t * cv) {

    task_node_t * this = cv->selected_task_node;
    task_node_t * next = cv->selected_task_node->next;

    if(next == NULL) {
        return;
    }

    int this_hours = (this->task->end.tm_hour - this->task->start.tm_hour);
    int this_mins = (this->task->end.tm_min - this->task->start.tm_min);
    int this_dur_mins = this_hours*60 + this_mins;

    int next_hours = (next->task->end.tm_hour - next->task->start.tm_hour);
    int next_mins = (next->task->end.tm_min - next->task->start.tm_min);
    int next_dur_mins = next_hours*60 + next_mins;

    int this_start_tm_hour_tmp = this->task->start.tm_hour;
    int this_start_tm_min_tmp = this->task->start.tm_min;

    this->task->end.tm_hour = next->task->end.tm_hour;
    this->task->end.tm_min  = next->task->end.tm_min;
    this->task->start.tm_hour = this->task->end.tm_hour - (this_dur_mins/60);
    this->task->start.tm_min  = this->task->end.tm_min - (this_dur_mins % 60);

    next->task->start.tm_hour = this_start_tm_hour_tmp;
    next->task->start.tm_min  = this_start_tm_min_tmp;
    next->task->end.tm_hour   = next->task->start.tm_hour + (next_dur_mins/60);
    next->task->end.tm_min    = next->task->start.tm_min + (next_dur_mins % 60);

    // swap the nodes in the doubly linked list

    task_node_t * this_prev = this->prev;
    task_node_t * next_next = next->next;

    this->next = next->next;
    next->prev = this->prev;
    this->prev = next;
    next->next = this;

    if(this_prev != NULL)
        this_prev->next = next;
    if(next_next != NULL)
        next_next->prev = this;
}

void renameTask(calendar_view_t * cv, const char * new_description) {
    free(cv->selected_task_node->task->description);
    cv->selected_task_node->task->description = strdup(new_description);
}

void cycleColor(calendar_view_t * cv) {

    int fg, bg;

    while(1) {
        fg = rand() % 8;
        bg = rand() % 8;
        if(good_colors[fg][bg] && bg != COLOR_BLACK)
            break;
    }

    cv->selected_task_node->task->fg_color = fg;
    cv->selected_task_node->task->bg_color = bg;
}

// TODO: this maybe should not be necessary; removing it it would require
//       that other code not assume that task pointers are contiguous
void makeTaskPointersContiguous(calendar_t * c) {
    size_t i, j;
    for(i=0; i < c->tasks_cap; i++) {
        if(c->tasks[i] == NULL) {
            break;
        }
        if(!c->tasks[i]->active) {
            for(j=i; j < c->tasks_cap-1; j++) { // slide all pointers down one slot
                c->tasks[j] = c->tasks[j+1];
            }
        }
    }

}

// TODO: clean up memory leaks
void deleteTask(calendar_view_t * cv) {
    task_node_t * next = cv->selected_task_node->next;
    task_node_t * prev = cv->selected_task_node->prev;
    cv->calendar->num_tasks--;
    cv->selected_task_node->task->active = 0;
    makeTaskPointersContiguous(cv->calendar); // maybe shouldn't be necessary

    if(prev != NULL) {
        prev->next = next;
    }
    if(next != NULL) {
        next->prev = prev;
    }

    if(next != NULL) {
        cv->selected_task_node = next;
    }
    else {
        cv->selected_task_node = prev;
    }
}

void addNewTask(calendar_view_t * cv) {

    // find a time to insert the task
    // look at the end time of the selected task
    // look at the start time of the next task
    // substract
    // if that time is greater than some minimum, 
    // insert the new task there

    if(cv->selected_task_node != NULL) {
        calendar_t * calendar = cv->calendar;
        struct tm * sel_end_tm = &cv->selected_task_node->task->end;
        time_t sel_end = mktime(sel_end_tm);
        if(cv->selected_task_node->next != NULL) {
            struct tm * next_start_tm = &cv->selected_task_node->next->task->start;
            time_t next_start = mktime(next_start_tm);
            double dt = difftime(next_start, sel_end);
            if(dt > 1800) { // 1800 seconds in 30 minutes
                task_t * newTask = allocTask(calendar);

                int fg, bg;
                int sel_bg = cv->selected_task_node->task->bg_color;
                while(1) {
                    fg = rand() % 8;
                    bg = rand() % 8;
                    if(good_colors[fg][bg] && (bg != sel_bg) && bg != COLOR_BLACK)
                        break;
                }

                newTask->description = strdup("New task");
                newTask->active = 1;

                newTask->start.tm_year  = 118;
                newTask->start.tm_mon   =   1;
                newTask->start.tm_wday  =   3;
                newTask->start.tm_mday  =  21;
                newTask->start.tm_yday  =  51;
                newTask->start.tm_isdst =   0;
                newTask->end.tm_year    = 118;
                newTask->end.tm_mon     =   1;
                newTask->end.tm_wday    =   3;
                newTask->end.tm_mday    =  21;
                newTask->end.tm_yday    =  51;
                newTask->end.tm_isdst   =   0;

                newTask->start.tm_hour  =  sel_end_tm->tm_hour;
                newTask->start.tm_min   =  sel_end_tm->tm_min;
                newTask->start.tm_sec   =  0;
                newTask->end.tm_hour    =  sel_end_tm->tm_hour;
                newTask->end.tm_min     =  sel_end_tm->tm_min + 30; // TODO
                newTask->end.tm_sec     =  0;
                newTask->fg_color       = fg;
                newTask->bg_color       = bg;

                addTask(calendar, newTask);
            }
        }
    }
}

void printCalendar(calendar_t * c) {
    size_t i;
    for(i=0; i < c->num_tasks; i++) {
        printf("task %d (%p): %s\nstart: %s",
               i, c->tasks[i], c->tasks[i]->description,
               asctime(&c->tasks[i]->start));
        printf("end: %s\n", asctime(&c->tasks[i]->end));
    }
}

void scrollView(calendar_view_t * cv, int amount) {
    cv->top.tm_min += amount;
    if(cv->top.tm_min >= 60) {
        cv->top.tm_min -= 60;
        cv->top.tm_hour++;
    }
    if(cv->top.tm_min < 0) {
        cv->top.tm_min += 60;
        cv->top.tm_hour--;
    }
    // don't scroll before or after midnight
    if(cv->top.tm_hour < 0) {
        cv->top.tm_hour = 0;
        cv->top.tm_min = 0;
    }
    // TODO: prevent scrolling past midnight (need rows)
    //if(cv->top.tm_hour > 23) {
    //    cv->top.tm_hour = 0;
    //    cv->top.tm_min = 0;
    //}
}

void renderCalendar(calendar_view_t * cv) {

    calendar_t * calendar = cv->calendar;

    int rows, cols;
    int r;
    size_t i, j;

    getmaxyx(stdscr, rows, cols);

    int top_hour = cv->top.tm_hour;
    int top_min = cv->top.tm_min;
    int top_row = (top_hour*60 + top_min)/5;

    int hours = top_hour;
    int minutes = top_min;
    int phours;
    char am_pm;

    for(r=0; r < rows; r++) {
        phours = (hours == 0) ? 12 : hours;
        phours = (hours == 12) ? 12 : (hours % 12);
        am_pm = (hours > 11) ? 'p' : 'a';
        mvwprintw(stdscr, r, 0, "%2d:%02d %cm ", phours, minutes, am_pm);
        minutes += 5;
        if(minutes >= 60) {
            minutes -= 60;
            hours++;
        }
    }

    for(i=0; i < calendar->num_tasks; i++) {
        int task_is_sel = (cv->selected_task_node->task == calendar->tasks[i]) ? 1 : 0;
        int start_hour = calendar->tasks[i]->start.tm_hour;
        int start_min  = calendar->tasks[i]->start.tm_min;
        int end_hour   = calendar->tasks[i]->end.tm_hour;
        int end_min    = calendar->tasks[i]->end.tm_min;
        int start_row  = (start_hour*60+start_min)/5 - top_row;
        int end_row    = (end_hour*60+end_min)/5 - top_row - 1; // TODO: deal with rounding
        int num_rows   = end_row - start_row + 1;
        int middle_row = start_row + num_rows/2 + (num_rows % 2) - 1;
        int fg_color   = calendar->tasks[i]->fg_color;
        int bg_color   = calendar->tasks[i]->bg_color;
        int width      = 30;
        int len        = strlen(calendar->tasks[i]->description);
        int desc_row   = (middle_row < 0) ? 0 : middle_row;
        desc_row   = (middle_row >= rows) ? rows-1 : desc_row;


        attron(COLOR_PAIR(fg_color*8+bg_color));
        for(r=start_row; r <= end_row; r++) {
            if(r >= rows || r < 0) {
                continue;
            }
            move(r, 10);
            for(j=0; j < 30; j++) {
                if(task_is_sel) {
                    if(r == start_row) {
                        if(j==0)         { waddch(stdscr, ACS_ULCORNER); }
                        else if(j == 29) { waddch(stdscr, ACS_URCORNER); }
                        else             { waddch(stdscr, ' ');          }
                    }
                    else if(r == end_row) {
                        if(j==0)         { waddch(stdscr, ACS_LLCORNER); }
                        else if(j == 29) { waddch(stdscr, ACS_LRCORNER); }
                        else             { waddch(stdscr, ' ');          }
                    }
                    else { waddch(stdscr, ' '); }
                }
                else { waddch(stdscr, ' '); }
            }
            if(r == desc_row) {
                if(task_is_sel) {
                    attron(A_BOLD);
                }
                mvwprintw(stdscr, r, 10 + (30 - len)/2, "%s", calendar->tasks[i]->description);
                if(task_is_sel) {
                    attroff(A_BOLD);
                }
            }
        }
        attroff(COLOR_PAIR(fg_color*8+bg_color));
    }

    refresh();
}

void sampleCalendar(calendar_t * c) {

    size_t i;
    task_t * ts[10];

    for(i=0; i < 6; i++) {
        ts[i] = allocTask(c);
        ts[i]->active = 1;
    }

    for(i=0; i < 6; i++) {
        ts[i]->start.tm_year  = 118;
        ts[i]->start.tm_mon   =   1;
        ts[i]->start.tm_wday  =   3;
        ts[i]->start.tm_mday  =  21;
        ts[i]->start.tm_yday  =  51;
        ts[i]->start.tm_isdst =   0;
        ts[i]->end.tm_year    = 118;
        ts[i]->end.tm_mon     =   1;
        ts[i]->end.tm_wday    =   3;
        ts[i]->end.tm_mday    =  21;
        ts[i]->end.tm_yday    =  51;
        ts[i]->end.tm_isdst   =   0;
    }

    ts[0]->description = strdup("First task");
    ts[0]->start.tm_hour  =  16;
    ts[0]->start.tm_min   =   5;
    ts[0]->start.tm_sec   =   0;
    ts[0]->end.tm_hour    =  16;
    ts[0]->end.tm_min     =  20;
    ts[0]->end.tm_sec     =   0;
    ts[0]->fg_color       = COLOR_BLACK;
    ts[0]->bg_color       = COLOR_RED;

    ts[1]->description = strdup("Second task");
    ts[1]->start.tm_hour  =  16;
    ts[1]->start.tm_min   =  25;
    ts[1]->start.tm_sec   =   0;
    ts[1]->end.tm_hour    =  16;
    ts[1]->end.tm_min     =  40;
    ts[1]->end.tm_sec     =   0;
    ts[1]->fg_color       = COLOR_BLACK;
    ts[1]->bg_color       = COLOR_GREEN;

    ts[2]->description = strdup("Third task");
    ts[2]->start.tm_hour  =  16;
    ts[2]->start.tm_min   =  45;
    ts[2]->start.tm_sec   =   0;
    ts[2]->end.tm_hour    =  17;
    ts[2]->end.tm_min     =  30;
    ts[2]->end.tm_sec     =   0;
    ts[2]->fg_color       = COLOR_BLACK;
    ts[2]->bg_color       = COLOR_YELLOW;

    ts[3]->description = strdup("Work");
    ts[3]->start.tm_hour  =   7;
    ts[3]->start.tm_min   =   0;
    ts[3]->start.tm_sec   =   0;
    ts[3]->end.tm_hour    =  12+3;
    ts[3]->end.tm_min     =  30;
    ts[3]->end.tm_sec     =   0;
    ts[3]->fg_color       = COLOR_YELLOW;
    ts[3]->bg_color       = COLOR_RED;

    ts[4]->description = strdup("Bed");
    ts[4]->start.tm_hour  =  12+9;
    ts[4]->start.tm_min   =   0;
    ts[4]->start.tm_sec   =   0;
    ts[4]->end.tm_hour    =  23;
    ts[4]->end.tm_min     =  59;
    ts[4]->end.tm_sec     =   0;
    ts[4]->fg_color       = COLOR_BLACK;
    ts[4]->bg_color       = COLOR_WHITE;

    ts[5]->description = strdup("Commute");
    ts[5]->start.tm_hour  =  12+3;
    ts[5]->start.tm_min   =  35;
    ts[5]->start.tm_sec   =   0;
    ts[5]->end.tm_hour    =  12+4;
    ts[5]->end.tm_min     =   0;
    ts[5]->end.tm_sec     =   0;
    ts[5]->fg_color       = COLOR_CYAN;
    ts[5]->bg_color       = COLOR_BLUE;

    for(i=0; i < 6; i++) {
        addTask(c, ts[i]);
    }

    for(i=0; i < c->num_tasks; i++) {
        printf("ts[%d]: %p\n", i, ts[i]);
    }
}

int main(int argc, char * argv[])
{
    int ch;
    int done = 0;
    size_t i, j;
    int r;
    int rows, cols;

    int load = 1;

    calendar_t calendar;
    calendar_view_t view;

    view.calendar = &calendar;
    view.granularity = 5;
    view.selected_task_node = NULL;
    view.hold_start = 0;
    view.hold_end = 0;
    view.top.tm_hour = 15;
    view.top.tm_min = 0;

    //time_t rawtime;
    //struct tm * timeinfo;
    //time ( &rawtime );
    //timeinfo = localtime ( &rawtime );
    //printf ( "Current local time and date: %s", asctime (timeinfo) );

    initCalendar(&calendar);

    if(load) {
        loadFromFile(&view);
    }
    else {
        sampleCalendar(&calendar);
    }

    //view.selected_task_node = calendar.head_task;

    task_node_t * node = calendar.head_task;
    for(i=0; i < calendar.num_tasks; i++) {
        printf("(%p):%s\nstart: %s", 
               node->task, node->task->description,
               asctime(&node->task->start));
        printf("end: %s\n", asctime(&node->task->end));
        node = node->next;
    }

    task_t * first_task = selectFirst(&view);

    selectUpcoming(&view);

    // printCalendar(&calendar);

    initscr();
    raw();
    keypad(stdscr, TRUE);
    noecho();
    curs_set(0); // hides the cursor
    mouseinterval(0); // TODO: make sure this is right
    mousemask(ALL_MOUSE_EVENTS | REPORT_MOUSE_POSITION, NULL);

    if(has_colors() == FALSE) {
        endwin();
        printf("Your terminal does not support color\n");
        exit(1);
    }
    start_color();

    size_t xx, yy;
    for(xx=0; xx<8; xx++) { 
        for(yy=0; yy<8; yy++) { 
            int index = xx*8+yy;
            init_pair(index, xx, yy);
        }
    }

    printf("\033[?1003h\n"); // Makes the terminal report mouse movement events

    renderCalendar(&view);

    MEVENT event;
    int mrow, mcol;
    int leftMouseDown = 0;

    int init_mrow, init_mcol, prev_mrow, prev_mcol;
    int dragShift = 0, startShift = 0, endShift = 0;

    int renamingTask = 0;
    //char buffer[512];
    size_t buf_idx = 0;

    while(!done) {

        if(renamingTask) {

            buf_idx = 0;
            free(view.selected_task_node->task->description);
            view.selected_task_node->task->description = calloc(512, sizeof(char));
            char * description = view.selected_task_node->task->description;
            renderCalendar(&view);

            // TODO: determine row and col to move cursor to
            //curs_set(1); // Terminal-specific high visibility mode
            //move(50,50);

            while((ch = wgetch(stdscr)) != '\n') {
                if(isprint(ch) || (ch == ' ')) {
                    description[buf_idx] = ch;
                    //buffer[buf_idx] = ch;
                    buf_idx++;
                }
                renderCalendar(&view);
                //move(50,50);
            }
            description[buf_idx] = '\0';
            //buffer[buf_idx] = '\0';
            //renameTask(&view, buffer);
            renamingTask = 0;
        }

        ch = wgetch(stdscr);
        const char * kn = keyname(ch);

        if(kn[0] == '^') {
            ch = '\0';
        }

        switch(ch) {
            case KEY_MOUSE:
                if (getmouse(&event) == OK) {

                    if(leftMouseDown) {
                        mcol = event.x;
                        mrow = event.y;
                        if(dragShift) {
                            shiftBy(&view, 5*(mrow - prev_mrow));
                        }
                        if(startShift) {
                            adjustStart(&view, 5*(mrow - prev_mrow));
                        }
                        if(endShift) {
                            adjustEnd(&view, 5*(mrow - prev_mrow));
                        }
                        prev_mrow = mrow;
                    }

                    if(event.bstate & BUTTON4_PRESSED) {
                        scrollView(&view, -15);
                    }
                    if(event.bstate & 0x100200000) {
                        scrollView(&view, 15);
                    }
                    if(event.bstate & (BUTTON1_CLICKED | BUTTON1_PRESSED)) {
                        mcol = event.x;
                        mrow = event.y;
                        selectByRowCol(&view, mrow, mcol);
                        if(first_row_clicked) startShift = 1;
                        else if(last_row_clicked) endShift = 1;
                        else dragShift = 1;
                    }
                    if(event.bstate & BUTTON1_PRESSED) {
                        leftMouseDown = 1;
                        init_mcol = event.x;
                        init_mrow = event.y;
                        prev_mcol = event.x;
                        prev_mrow = event.y;
                    }
                    if(event.bstate & BUTTON1_RELEASED) {
                        leftMouseDown = 0;
                        startShift = 0;
                        endShift = 0;
                        dragShift = 0;
                    }
                }
                break;
            case KEY_UP:
                shiftBy(&view, -5);
                break;
            case KEY_DOWN:
                shiftBy(&view, 5);
                break;
            case KEY_LEFT:
                selectPrev(&view);
                //view.hold_start = !view.hold_start;
                break;
            case KEY_RIGHT:
                selectNext(&view);
                //view.hold_end = !view.hold_end;
                break;
            case KEY_HOME:
                //expandUp();
                break;
            case KEY_END:
                //expandDown();
                break;
            case KEY_DC:
                deleteTask(&view);
                break;
            case KEY_PPAGE:
                scrollView(&view, -60);
                break;
            case KEY_NPAGE:
                scrollView(&view, 60);
                break;
            case '\0': // check kn[1]
                if(kn[1] == 'U') {
                    expandUp(&view);
                }
                else if(kn[1] == 'D') {
                    expandDown(&view);
                }
                else if(kn[1] == 'J') {
                    //swapWithNext(&view);
                    renamingTask = 1;
                }
                break;
            case 'U':
                shiftUpToPrev(&view);
                break;
            case 'D':
                shiftDownToNext(&view);
                break;
            case 'j':
                scrollView(&view, 5);
                break;
            case 'k':
                scrollView(&view, -5);
                break;
            case 'S':
                swapWithNext(&view);
                break;
            case 'f':
                saveToFile(&view);
                break;
                //case 'l':
                //    loadFromFile(&view);
                //    break;
            case 'c':
                cycleColor(&view);
                break;
            case '`':
                //selectPrev(&view);
                break;
            case '\n':
                // NOTE: see ^J
                break;
            case '\t':
                //selectNext(&view);
                break;
            case 'n':
                addNewTask(&view);
                break;
            case 'q':
                done = 1;
                break;
            default:
                //printw("Key %d, hopefully printable as %c\n", ch, ch);
                break;
        }

        werase(stdscr);
        renderCalendar(&view);
    }

    printf("\033[?1003l\n"); // Disable mouse movement events, as l = low

    endwin();

    return 0;
}
