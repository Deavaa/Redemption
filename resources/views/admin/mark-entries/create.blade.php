@extends('layouts.admin')
@section('content')
    <div style="padding:8px;">
        <style>
            #markCard label {
                display: block !important;
                margin-bottom: 0 !important;
                padding: 0 !important;
                font-size: 10px !important;
            }

            #markCard label small {
                display: inline !important;
                line-height: 1 !important;
                margin: 0 !important;
                font-size: 9px !important;
            }

            #markCard input.mi,
            #markCard input[type="number"] {
                margin: 0 !important;
                padding: 4px 6px !important;
                line-height: 1.6 !important;
            }

            .ca-item {
                display: flex;
                align-items: center;
                gap: 4px;
                margin-bottom: 4px;
            }

            .ca-item .ca-badge {
                width: 1.6rem;
                min-width: 1.6rem;
                height: 1.8rem;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                background: #2980b9;
                color: #fff;
                border-radius: 0.35rem;
                font-size: 0.75rem;
                font-weight: 700;
            }

            .ca-extra-row {
                display: grid;
                grid-template-columns: repeat(3, minmax(0, 1fr));
                gap: 6px;
                margin-top: 8px;
            }

            #markCard {
                background: #eff6ff !important;
            }

            #markCard .student-header {
                display: flex;
                justify-content: space-between;
                align-items: flex-start;
                background: linear-gradient(135deg, #0e3a65 0%, #174c87 100%) !important;
                color: #ffffff !important;
                padding: 14px 16px !important;
                border-radius: 0.75rem !important;
                margin-bottom: 12px !important;
                box-shadow: 0 5px 16px rgba(14, 51, 83, 0.2) !important;
                overflow: visible !important;
                min-height: 72px !important;
            }

            #markCard .student-header h3,
            #markCard .student-header span {
                color: #ffffff !important;
                margin: 0 !important;
            }

            #markCard .student-meta {
                margin-top: 4px;
                display: grid;
                gap: 2px;
            }

            #markCard .student-meta span {
                font-size: 0.78rem !important;
                opacity: 0.85 !important;
                display: block;
            }

            #markCard .student-header h3 {
                font-size: 1.6rem !important;
                font-weight: 800 !important;
                letter-spacing: 0.02em !important;
                word-break: break-word !important;
                text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2) !important;
            }

            #markCard .student-meta span {
                display: block !important;
                font-size: 0.85rem !important;
                opacity: 0.9 !important;
            }
        </style>


        <!-- Filter Bar -->
        <div style="background:#fff;padding:5px;box-shadow:0 1px 4px rgba(0,0,0,.08);margin-bottom:4px;">
            <div style="display:flex;flex-wrap:wrap;gap:10px;align-items:flex-end;">
                <div style="flex:1;min-width:150px;">
                    <label style="display:none;" for="sel_ay">Academic Year</label>
                    <select id="sel_ay" aria-label="Academic Year" style="width:100%;padding:4px;border:1px solid #ccc;">
                        <option value="">-- Select --</option>
                        @foreach ($academicYears as $ay)
                            <option value="{{ $ay->id }}">{{ $ay->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div style="flex:1;min-width:120px;">
                    <label style="display:none;" for="sel_term">Term</label>
                    <select id="sel_term" aria-label="Term" style="width:100%;padding:4px;border:1px solid #ccc;">
                        <option value="">-- Year First --</option>
                    </select>
                </div>
                <div style="flex:1;min-width:150px;">
                    <label style="display:none;" for="sel_subject">Subject</label>
                    <select id="sel_subject" name="subject_id" aria-label="Subject"
                        style="width:100%;padding:4px;border:1px solid #ccc;">
                        <option value="">-- Select --</option>
                        @foreach ($subjects as $s)
                            <option value="{{ $s->id }}">{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div style="flex:1;min-width:100px;">
                    <label style="display:none;" for="sel_grade">Class</label>
                    <select id="sel_grade" aria-label="Class" style="width:100%;padding:4px;border:1px solid #ccc;">
                        <option value="">-- Select --</option>
                        @foreach ($classGrades as $g)
                            <option value="{{ $g }}">{{ $g }}</option>
                        @endforeach
                    </select>
                </div>
                <div style="flex:1;min-width:100px;">
                    <label style="display:none;" for="sel_section">Section</label>
                    <select id="sel_section" aria-label="Section" style="width:100%;padding:8px;border:1px solid #ccc;">
                        <option value="">-- Class First --</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Student Nav -->
        <div id="navBar"
            style="display:none;background:#2c3e50;color:#fff;padding:6px 8px;display:flex;justify-content:space-between;align-items:center;">
            <button onclick="goPrev()"
                style="background:rgba(255,255,255,.2);border:none;color:#fff;padding:3px 4px;cursor:pointer;font-size:8px;"><i
                    class="fas fa-chevron-left"></i> Prev</button>
            <span id="navCounter" style="font-weight:600;font-size:12px;">1 / 1</span>
            <button onclick="goNext()"
                style="background:rgba(255,255,255,.2);border:none;color:#fff;padding:3px 4px;cursor:pointer;font-size:8px;">Next
                <i class="fas fa-chevron-right"></i></button>
        </div>

        <!-- Mark Card -->
        <div id="markCard" style="display:none;padding:4px;box-shadow:0 1px 6px rgba(0,0,0,.12);">
            <div class="student-header">
                <div>
                    <h3 id="studentName" style="margin:0;font-size:1.1rem;">--</h3>
                    <div class="student-meta">
                        <span id="studentAdm" style="font-size:0.8rem;opacity:.8;">--</span>
                        <span id="studentSubject" style="display:block;">--</span>
                        <span id="studentYear" style="display:block;">--</span>
                    </div>
                </div>
                <div style="display:flex;align-items:center;gap:10px;">
                    <button id="saveBtn" type="button" onclick="saveMarks()"
                        style="background:#27ae60;color:#fff;border:none;padding:8px 14px;border-radius:0.35rem;cursor:pointer;font-weight:700;font-size:0.95rem;">
                        Save All
                    </button>
                    <div id="saveStatus"
                        style="display:none;padding:8px 12px;font-size:0.92rem;font-weight:600;display:inline-block;border-radius:0.35rem;min-width:90px;text-align:center;">
                    </div>
                </div>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
                <!-- CA Column -->
                <div>
                    <h4 style="color:#2980b9;margin:0 0 2x 0;font-size:0.9rem;border-bottom:2px solid #2980b9;">
                        Continuous Assessment <span style="font-weight:normal;font-size:0.8rem;">(Raw /70 &rarr; Scaled
                            /30)</span></h4>
                    <div style="display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:6px;">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-primary text-white">1</span>
                            <input type="number" data-field="ca1" data-group="ca" min="0" max="5"
                                step="0.5" class="form-control mi" placeholder="/5"
                                style="border:1px solid #ddd;box-sizing:border-box;">
                        </div>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-primary text-white">2</span>
                            <input type="number" data-field="ca2" data-group="ca" min="0" max="5"
                                step="0.5" class="form-control mi" placeholder="/5"
                                style="border:1px solid #ddd;box-sizing:border-box;">
                        </div>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-primary text-white">3</span>
                            <input type="number" data-field="ca3" data-group="ca" min="0" max="5"
                                step="0.5" class="form-control mi" placeholder="/5"
                                style="border:1px solid #ddd;box-sizing:border-box;">
                        </div>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-primary text-white">4</span>
                            <input type="number" data-field="ca4" data-group="ca" min="0" max="5"
                                step="0.5" class="form-control mi" placeholder="/5"
                                style="border:1px solid #ddd;box-sizing:border-box;">
                        </div>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-primary text-white">5</span>
                            <input type="number" data-field="ca5" data-group="ca" min="0" max="5"
                                step="0.5" class="form-control mi" placeholder="/5"
                                style="border:1px solid #ddd;box-sizing:border-box;">
                        </div>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-primary text-white">6</span>
                            <input type="number" data-field="ca6" data-group="ca" min="0" max="5"
                                step="0.5" class="form-control mi" placeholder="/5"
                                style="border:1px solid #ddd;box-sizing:border-box;">
                        </div>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-primary text-white">7</span>
                            <input type="number" data-field="ca7" data-group="ca" min="0" max="5"
                                step="0.5" class="form-control mi" placeholder="/5"
                                style="border:1px solid #ddd;box-sizing:border-box;">
                        </div>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-primary text-white">8</span>
                            <input type="number" data-field="ca8" data-group="ca" min="0" max="5"
                                step="0.5" class="form-control mi" placeholder="/5"
                                style="border:1px solid #ddd;box-sizing:border-box;">
                        </div>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-primary text-white">9</span>
                            <input type="number" data-field="ca9" data-group="ca" min="0" max="5"
                                step="0.5" class="form-control mi" placeholder="/5"
                                style="border:1px solid #ddd;box-sizing:border-box;">
                        </div>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-primary text-white">10</span>
                            <input type="number" data-field="ca10" data-group="ca" min="0" max="5"
                                step="0.5" class="form-control mi" placeholder="/5"
                                style="border:1px solid #ddd;box-sizing:border-box;">
                        </div>
                    </div>
                    <div class="ca-extra-row">
                        <div>
                            <label style="font-size:10px;display:block;margin-bottom:3px;">Conduct</label>
                            <input type="number" data-field="conduct" data-group="ca" min="0" max="5"
                                step="0.5" class="mi" placeholder="/5"
                                style="width:100%;border:1px solid #ddd;box-sizing:border-box;">
                        </div>
                        <div>
                            <label style="font-size:10px;display:block;margin-bottom:3px;">Handwriting</label>
                            <input type="number" data-field="handwriting" data-group="ca" min="0"
                                max="5" step="0.5" class="mi" placeholder="/5"
                                style="width:100%;border:1px solid #ddd;box-sizing:border-box;">
                        </div>
                        <div>
                            <label style="font-size:10px;display:block;margin-bottom:3px;">Creativity</label>
                            <input type="number" data-field="creativity" data-group="ca" min="0" max="10"
                                step="0.5" class="mi" placeholder="/10"
                                style="width:100%;border:1px solid #ddd;box-sizing:border-box;">
                        </div>
                    </div>
                </div>

                <!-- Exam Column -->
                <div>
                    <h4
                        style="color:#27ae60;margin:0 0 10px 0;font-size:0.9rem;border-bottom:2px solid #27ae60;padding-bottom:4px;">
                        Examination <span style="font-weight:normal;font-size:0.8rem;">(/70)</span></h4>
                    <div style="display:flex;flex-direction:column;gap:12px;">
                        <div><label style="font-size:10px;display:block;margin-bottom:2px;">Test 1
                                <small>/10</small></label><input type="number" data-field="test1" data-group="exam"
                                min="0" max="10" step="0.5" class="mi"
                                style="width:100%;padding:7px;border:1px solid #ddd;box-sizing:border-box;">
                        </div>
                        <div><label style="font-size:10px;display:block;margin-bottom:2px;">Test 2
                                <small>/10</small></label><input type="number" data-field="test2" data-group="exam"
                                min="0" max="10" step="0.5" class="mi"
                                style="width:100%;padding:7px;border:1px solid #ddd;box-sizing:border-box;">
                        </div>
                        <div><label style="font-size:10px;display:block;margin-bottom:2px;">Mid-Term
                                <small>/20</small></label><input type="number" data-field="mid_term" data-group="exam"
                                min="0" max="20" step="0.5" class="mi"
                                style="width:100%;padding:7px;border:1px solid #ddd;box-sizing:border-box;">
                        </div>
                        <div><label style="font-size:10px;display:block;margin-bottom:2px;">Final Exam
                                <small>/30</small></label><input type="number" data-field="final_exam" data-group="exam"
                                min="0" max="30" step="0.5" class="mi"
                                style="width:100%;padding:7px;border:1px solid #ddd;box-sizing:border-box;">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Totals Bar -->
            <div style="margin-top:12px;padding:10px 14px;background:linear-gradient(135deg,#2c3e50,#34495e);color:#fff;">
                <div style="display:flex;flex-wrap:wrap;gap:14px;justify-content:space-between;align-items:center;">
                    <div><span style="opacity:.7;font-size:11px;">CA Raw</span><br><span id="tCaRaw"
                            style="font-size:18px;font-weight:700;">0</span><span
                            style="opacity:.7;font-size:12px;">/70</span></div>
                    <div style="font-size:18px;opacity:.5;">&rarr;</div>
                    <div><span style="opacity:.7;font-size:11px;">CA Scaled</span><br><span id="tCaScaled"
                            style="font-size:18px;font-weight:700;">0</span><span
                            style="opacity:.7;font-size:12px;">/30</span></div>
                    <div style="font-size:24px;opacity:.5;">+</div>
                    <div><span style="opacity:.7;font-size:11px;">Exam Total</span><br><span id="tExam"
                            style="font-size:18px;font-weight:700;">0</span><span
                            style="opacity:.7;font-size:12px;">/70</span></div>
                    <div style="font-size:24px;opacity:.5;">=</div>
                    <div><span style="opacity:.7;font-size:11px;">Term Total</span><br><span id="tTotal"
                            style="font-size:22px;font-weight:700;">0</span><span
                            style="opacity:.7;font-size:12px;">/100</span></div>
                    <div style="text-align:center;min-width:80px;">
                        <span style="opacity:.7;font-size:11px;display:block;">Grade</span>
                        <span id="tGrade" style="font-size:24px;font-weight:700;">-</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Empty State -->
        <div id="emptyState"
            style="background:#fff;padding:20px 12px;text-align:center;box-shadow:0 1px 4px rgba(0,0,0,.08);color:#aaa;">
            <i class="fas fa-hand-pointer" style="font-size:36px;margin-bottom:10px;display:block;"></i>

        </div>
    </div>

    <meta name="csrf-token" content="{{ csrf_token() }}">
@endsection

@section('scripts')
    <script>
        var students = [],
            marksData = {},
            curIdx = 0,
            saveTimer = null;
        var csrf = document.querySelector('meta[name="csrf-token"]').content;

        document.getElementById('sel_ay').addEventListener('change', function() {
            if (!this.value) {
                document.getElementById('sel_term').innerHTML = '<option value="">-- Year First --</option>';
                return;
            }
            fetch('/admin/mark-entries/api/terms?academic_year_id=' + this.value).then(function(r) {
                return r.json();
            }).then(function(terms) {
                var s = document.getElementById('sel_term');
                s.innerHTML = '<option value="">-- Select Term --</option>';
                terms.forEach(function(t) {
                    s.innerHTML += '<option value="' + t.id + '">' + t.name + '</option>';
                });
            });
        });

        document.getElementById('sel_grade').addEventListener('change', function() {
            if (!this.value) {
                document.getElementById('sel_section').innerHTML = '<option value="">-- Class First --</option>';
                return;
            }
            fetch('/admin/mark-entries/api/sections?class_grade=' + encodeURIComponent(this.value)).then(function(
                r) {
                return r.json();
            }).then(function(secs) {
                var s = document.getElementById('sel_section');
                s.innerHTML = '<option value="">-- Select --</option>';
                secs.forEach(function(v) {
                    s.innerHTML += '<option value="' + v + '">' + v + '</option>';
                });
                tryAutoLoad();
            });
        });

        document.getElementById('sel_ay').addEventListener('change', tryAutoLoad);
        document.getElementById('sel_term').addEventListener('change', tryAutoLoad);
        document.getElementById('sel_section').addEventListener('change', tryAutoLoad);
        document.getElementById('sel_subject').addEventListener('change', tryAutoLoad);

        function tryAutoLoad() {
            var ay = document.getElementById('sel_ay').value,
                tm = document.getElementById('sel_term').value,
                su = document.getElementById('sel_subject').value,
                gr = document.getElementById('sel_grade').value,
                se = document.getElementById('sel_section').value;
            if (ay && tm && su && gr && se) {
                loadStudents();
            }
        }

        function loadStudents() {
            var ay = document.getElementById('sel_ay').value,
                tm = document.getElementById('sel_term').value,
                su = document.getElementById('sel_subject').value,
                gr = document.getElementById('sel_grade').value,
                se = document.getElementById('sel_section').value;
            if (!ay || !tm || !su || !gr || !se) {
                return;
            }
            fetch('/admin/mark-entries/api/students?academic_year_id=' + ay + '&term_id=' + tm + '&subject_id=' + su +
                '&class_grade=' + encodeURIComponent(gr) + '&section=' + encodeURIComponent(se)).then(function(r) {
                return r.json();
            }).then(function(d) {
                students = d.students || [];
                marksData = d.marks || {};
                if (!students.length) {
                    alert('No students found for this selection.');
                    return;
                }
                curIdx = 0;
                showStudent(0);
                document.getElementById('navBar').style.display = 'flex';
                document.getElementById('markCard').style.display = 'block';
                document.getElementById('emptyState').style.display = 'none';
            });
        }

        function showStudent(i) {
            var s = students[i];
            var studentName = [s.first_name || s.student_name || s.name, s.last_name].filter(Boolean).join(' ') ||
                'Student';
            var subjectSelect = document.getElementById('sel_subject');
            var yearSelect = document.getElementById('sel_ay');
            var subjectText = subjectSelect.selectedOptions.length ? subjectSelect.selectedOptions[0].text : '--';
            var yearText = yearSelect.selectedOptions.length ? yearSelect.selectedOptions[0].text : '--';
            document.getElementById('studentName').textContent = studentName;
            document.getElementById('studentAdm').textContent = s.admission_number || '';
            document.getElementById('studentSubject').textContent = 'Subject: ' + subjectText;
            document.getElementById('studentYear').textContent = 'Year: ' + yearText;
            document.getElementById('navCounter').textContent = (i + 1) + ' / ' + students.length;
            document.querySelectorAll('.mi').forEach(function(inp) {
                inp.value = '';
            });
            var m = marksData[s.id];
            if (m) {
                document.querySelectorAll('.mi').forEach(function(inp) {
                    var f = inp.dataset.field;
                    if (m[f] !== null && m[f] !== undefined) {
                        inp.value = m[f];
                    }
                });
            }
            recalc();
        }

        function goPrev() {
            if (curIdx > 0) {
                curIdx--;
                showStudent(curIdx);
            }
        }

        function goNext() {
            if (curIdx < students.length - 1) {
                curIdx++;
                showStudent(curIdx);
            }
        }

        function recalc() {
            var caR = 0,
                exT = 0;
            document.querySelectorAll('.mi').forEach(function(inp) {
                var v = parseFloat(inp.value) || 0;
                if (inp.dataset.group === 'ca') caR += v;
                else exT += v;
            });
            var caS = (caR / 70) * 30,
                tot = caS + exT;
            var g = 'F';
            if (tot >= 90) g = 'A+';
            else if (tot >= 80) g = 'A';
            else if (tot >= 75) g = 'A-';
            else if (tot >= 70) g = 'B+';
            else if (tot >= 65) g = 'B';
            else if (tot >= 60) g = 'B-';
            else if (tot >= 55) g = 'C+';
            else if (tot >= 50) g = 'C';
            else if (tot >= 45) g = 'C-';
            else if (tot >= 40) g = 'D';
            document.getElementById('tCaRaw').textContent = caR.toFixed(1);
            document.getElementById('tCaScaled').textContent = caS.toFixed(1);
            document.getElementById('tExam').textContent = exT.toFixed(1);
            document.getElementById('tTotal').textContent = tot.toFixed(1);
            var ge = document.getElementById('tGrade');
            ge.textContent = g;
            ge.style.color = g === 'F' ? '#e74c3c' : g.startsWith('A') ? '#2ecc71' : g.startsWith('B') ? '#3498db' : g
                .startsWith('C') ? '#f39c12' : '#e67e22';
        }

        function saveField(field, value) {
            if (!students.length) return;
            var s = students[curIdx];
            var st = document.getElementById('saveStatus');
            var d = {
                student_id: s.id,
                subject_id: document.getElementById('sel_subject').value,
                academic_year_id: document.getElementById('sel_ay').value,
                term_id: document.getElementById('sel_term').value,
                class_grade: document.getElementById('sel_grade').value,
                section: document.getElementById('sel_section').value,
                mark_key: field,
                mark_value: value || null
            };
            marksData[s.id] = marksData[s.id] || {};
            marksData[s.id][field] = value || null;
            st.style.display = 'inline-block';
            st.textContent = 'Saving...';
            st.style.background = '#f39c12';
            st.style.color = '#ffffff';
            fetch('/admin/mark-entries/api/save', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf
                    },
                    body: JSON.stringify(d)
                })
                .then(function(r) {
                    return r.json();
                }).then(function(res) {
                    if (res.success) {
                        st.textContent = 'Saved!';
                        st.style.background = '#27ae60';
                        marksData[s.id] = res.entry || marksData[s.id];
                        setTimeout(function() {
                            st.style.display = 'none';
                        }, 1500);
                    } else {
                        st.textContent = 'Error: ' + (res.error || 'Failed');
                        st.style.background = '#e74c3c';
                    }
                }).catch(function() {
                    st.textContent = 'Network Error';
                    st.style.background = '#e74c3c';
                });
        }

        function saveMarks() {
            if (!students.length) return;
            var s = students[curIdx],
                d = {};
            d.student_id = s.id;
            d.subject_id = document.getElementById('sel_subject').value;
            d.academic_year_id = document.getElementById('sel_ay').value;
            d.term_id = document.getElementById('sel_term').value;
            d.class_grade = document.getElementById('sel_grade').value;
            d.section = document.getElementById('sel_section').value;
            document.querySelectorAll('.mi').forEach(function(inp) {
                d[inp.dataset.field] = inp.value || null;
            });
            var st = document.getElementById('saveStatus');
            marksData[s.id] = d;
            st.style.display = 'inline-block';
            st.textContent = 'Saving...';
            st.style.background = '#f39c12';
            st.style.color = '#ffffff';
            fetch('/admin/mark-entries/api/save', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf
                    },
                    body: JSON.stringify(d)
                })
                .then(function(r) {
                    return r.json();
                }).then(function(res) {
                    if (res.success) {
                        st.textContent = 'Saved!';
                        st.style.background = '#27ae60';
                        marksData[s.id] = res.entry || d;
                        setTimeout(function() {
                            st.style.display = 'none';
                        }, 1500);
                    } else {
                        st.textContent = 'Error: ' + (res.error || 'Failed');
                        st.style.background = '#e74c3c';
                    }
                }).catch(function() {
                    st.textContent = 'Network Error';
                    st.style.background = '#e74c3c';
                });
        }

        function initMarkEntry() {
            document.querySelectorAll('.mi').forEach(function(inp) {
                inp.addEventListener('blur', function() {
                    var mx = parseFloat(this.max),
                        v = parseFloat(this.value);
                    if (!isNaN(v) && v > mx) this.value = mx;
                    if (!isNaN(v) && v < 0) this.value = 0;
                    recalc();
                    if (saveTimer) {
                        clearTimeout(saveTimer);
                        saveTimer = null;
                    }
                    saveField(this.dataset.field, this.value);
                });
                inp.addEventListener('input', function() {
                    recalc();
                    var self = this;
                    if (saveTimer) clearTimeout(saveTimer);
                    saveTimer = setTimeout(function() {
                        saveField(self.dataset.field, self.value);
                    }, 800);
                });
            });
            document.addEventListener('keydown', function(e) {
                if (e.target.tagName === 'INPUT') return;
                if (e.key === 'ArrowLeft') goPrev();
                if (e.key === 'ArrowRight') goNext();
            });
            var txS = 0;
            var mc = document.getElementById('markCard');
            if (mc) {
                mc.addEventListener('touchstart', function(e) {
                    txS = e.touches[0].clientX;
                });
                mc.addEventListener('touchend', function(e) {
                    var diff = e.changedTouches[0].clientX - txS;
                    if (Math.abs(diff) > 60) {
                        if (diff > 0) goPrev();
                        else goNext();
                    }
                });
            }
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initMarkEntry);
        } else {
            initMarkEntry();
        }
    </script>
@endsection
