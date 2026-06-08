/* =====================================================
   ProSensia Interactive Character Engine v2
   Buzz (AC unit · male · blue) — Gigi (Geyser · female · orange)
   WizardController — QA Bank — Debate Engine — Web Speech
   ===================================================== */

// ===== CHARACTER DEFINITIONS =====
const PS_CHARS = {
  ac: {
    id: 'ac', name: 'Buzz', title: 'AI & IoT Guide',
    voiceGender: 'male', accentColor: '#29b6f6',
    lines: {
      greeting:      n  => `Hey ${n}! I'm Buzz — your AI & IoT guide here at ProSensia! Ready to crush today? Let's GO! ❄️`,
      noTasks:       n  => `Hey ${n}! No tasks are scheduled for today. Great time to review your Kanban board or explore Materials!`,
      taskIntro:     (n,ti,m) => `Alright ${n}! Today's mission: "${ti}". Estimated: ${m} min. I'll guide you every step of the way!`,
      waitUnlock:    t  => `Hey! Tasks unlock at 9:00 AM sharp. It's ${t} right now — mark your attendance while we wait! ❄️`,
      noAttendance:  n  => `Good morning ${n}! Before we dive into tasks, please check in first. ProSensia tracks attendance every day!`,
      videoPrompt:   ti => `Watch the resource for "${ti}" first — it'll give you context. Come back and hit Continue when done! 📺`,
      complete:      (n,all) => all
        ? `AMAZING work ${n}!! You completed ALL of today's tasks — that's the engineer mindset! 🎉`
        : `Great job ${n}! Let's keep the momentum going!`,
      linkedin:      n  => `Awesome ${n}! Share this win on LinkedIn — it builds your professional brand! Then check out when ready.`,
      debate:        () => `Let's think deeper! Two perspectives — which do you agree with more?`,
      encouragement: ['Excellent!', 'You\'re crushing it!', 'That\'s the engineer mindset!', 'Outstanding!', 'You\'re on fire! 🔥'],
      correct:       ['Correct! Great thinking! 💡', 'Exactly right!', 'Nailed it! ✅', 'That\'s the answer!'],
      wrong:         ['Not quite — but great try!', 'Almost! Here\'s the key insight:', 'Good effort! Let me explain:'],
    }
  },
  geyser: {
    id: 'geyser', name: 'Gigi', title: 'Learning Guide',
    voiceGender: 'female', accentColor: '#ff7043',
    lines: {
      greeting:      n  => `Hiii ${n}! I'm Gigi — your learning guide at ProSensia! SO excited to work with you today! 💫`,
      noTasks:       n  => `Hi ${n}! Free slot today! How about brushing up on Materials or updating your Kanban board? 🌟`,
      taskIntro:     (n,ti,m) => `Ooh ${n}, today's task: "${ti}"! About ${m} minutes. Let's make it amazing together!`,
      waitUnlock:    t  => `Good morning! Tasks will be ready at 9:00 AM. It's ${t} right now — mark attendance first! 😊`,
      noAttendance:  n  => `Hi ${n}! Don't forget to check in before we start! It only takes a second! 😊`,
      videoPrompt:   ti => `Before we continue with "${ti}", please watch the resource below. I'll be right here when you're done! 🎬`,
      complete:      (n,all) => all
        ? `YOU DID IT ${n}!! All tasks done — you're absolutely INCREDIBLE today! 🎉🌟`
        : `Woohoo ${n}! Great job! Let's keep this energy going!`,
      linkedin:      n  => `${n}, you HAVE to share this on LinkedIn! Your network needs to see how hard you're working! 💼`,
      debate:        () => `Brain exercise time! Two ideas — which do you agree with more?`,
      encouragement: ['Brilliant! ✨', 'You\'re glowing! 💫', 'That\'s what I\'m talking about!', 'Wow, amazing!', 'Superstar! ⭐'],
      correct:       ['Yes! Perfect! ✨', 'You got it!', 'Amazing — you know your stuff!', 'Absolutely right!'],
      wrong:         ['Oops, not quite! But don\'t worry…', 'Almost! Let me help…', 'Good try! Here\'s the secret:'],
    }
  }
};

// ===== Q&A KNOWLEDGE BANK =====
const QA_BANK = [
  { id:'iot1',  kw:['iot','sensor','mqtt','ac','predictive','maintenance','geyser','energy','hvac'],
    q:'What does IoT stand for?',
    opts:['Internet of Things','Integrated Output Terminal','Input/Output Technology','Internet of Triggers'],
    correct:0,
    explain:'IoT (Internet of Things) connects physical devices to the internet. ProSensia uses IoT sensors in AC systems to monitor temperature, vibration & power in real time for predictive maintenance!' },

  { id:'pred1', kw:['predictive','failure','anomaly','fault','detect','diagnos'],
    q:'What is the main benefit of AI-based predictive maintenance?',
    opts:['Predict failures before they happen','Fix devices after they break','Replace old equipment','Save electricity only'],
    correct:0,
    explain:'Predictive maintenance uses AI to detect anomalies BEFORE equipment fails — saving costs, preventing downtime, and extending equipment life. This is ProSensia\'s core product!' },

  { id:'api1',  kw:['api','rest','endpoint','http','request','response','backend','express','node'],
    q:'What does REST in REST API stand for?',
    opts:['Representational State Transfer','Real-time Event Sync Transfer','Remote Execution of Secure Tasks','Request-Execute-Send-Transfer'],
    correct:0,
    explain:'REST (Representational State Transfer) uses standard HTTP methods (GET, POST, PUT, DELETE) for client-server communication. It is stateless, scalable, and the standard for web APIs.' },

  { id:'db1',   kw:['database','sql','mysql','query','table','schema','crud','join'],
    q:'Which SQL clause is used to filter rows from a SELECT query?',
    opts:['WHERE','FILTER','HAVING','SEARCH'],
    correct:0,
    explain:'WHERE filters rows based on a condition before grouping. HAVING filters after GROUP BY. Always use WHERE for row-level filtering to keep queries efficient!' },

  { id:'git1',  kw:['git','github','version','commit','push','branch','pull','merge','repo'],
    q:'What git command stages all changed files for the next commit?',
    opts:['git add .','git commit -a','git stage all','git push origin'],
    correct:0,
    explain:'"git add ." stages all modified and new files. Then "git commit -m \'message\'" saves the snapshot. Always write meaningful commit messages — future-you will thank you!' },

  { id:'ai1',   kw:['ai','machine learning','model','neural','training','dataset','classify','predict','ml'],
    q:'What distinguishes machine learning from traditional programming?',
    opts:['Learning patterns from data without explicit rules','Running faster on modern CPUs','Automatically writing source code','Replacing all human developers'],
    correct:0,
    explain:'ML algorithms learn patterns from data instead of following hand-coded rules. This makes them ideal for complex problems like anomaly detection, image recognition, and predictive maintenance.' },

  { id:'react1',kw:['react','component','jsx','frontend','ui','state','hook','useState','useEffect'],
    q:'In React, which hook manages local component state?',
    opts:['useState','useEffect','useRef','useContext'],
    correct:0,
    explain:'useState returns [value, setValue]. Calling setValue triggers a re-render with the new value. useEffect handles side effects (API calls, timers). Both are React\'s most essential hooks!' },

  { id:'cyber1',kw:['security','cyber','hash','password','encrypt','vulnerability','xss','injection','auth'],
    q:'Which algorithm is recommended for securely storing passwords?',
    opts:['bcrypt','MD5','SHA-1','Base64 encoding'],
    correct:0,
    explain:'bcrypt is intentionally slow and salted — perfect for passwords. MD5/SHA-1 are too fast (brute-forceable). Base64 is encoding, NOT encryption. Always use bcrypt (or Argon2) for passwords!' },

  { id:'php1',  kw:['php','laravel','backend','session','pdo','prepare','statement','server'],
    q:'What does PDO stand for in PHP?',
    opts:['PHP Data Objects','PHP Database Operations','Prepared Data Output','PHP Driver Objects'],
    correct:0,
    explain:'PDO (PHP Data Objects) provides a consistent database interface with prepared statements that prevent SQL injection. Always use PDO->prepare() instead of raw string queries!' },

  { id:'css1',  kw:['css','style','flexbox','grid','layout','responsive','bootstrap','design','tailwind'],
    q:'Which CSS property creates a flexible, responsive row/column layout?',
    opts:['display: flex','display: block','float: left','position: absolute'],
    correct:0,
    explain:'Flexbox (display: flex) is the modern layout system for dynamic UI. Use flex-direction, justify-content, and align-items to control alignment. CSS Grid is even more powerful for 2D layouts!' },

  { id:'py1',   kw:['python','pandas','numpy','data','analysis','script','automation','django','flask'],
    q:'Which Python library is used for data analysis and DataFrames?',
    opts:['pandas','numpy','matplotlib','scikit-learn'],
    correct:0,
    explain:'pandas provides DataFrames for data manipulation. numpy handles numerical ops. matplotlib handles visualization. scikit-learn provides ML models. Together they power data science workflows!' },

  { id:'qa1',   kw:['test','qa','quality','bug','selenium','unit','automation','testing','verify'],
    q:'What is the purpose of unit testing?',
    opts:['Test individual functions in isolation','Test the entire app end-to-end','Check UI renders correctly','Monitor server performance'],
    correct:0,
    explain:'Unit tests verify individual functions work correctly in isolation. Integration tests check component interactions. E2E tests simulate real user flows. All three are important for quality software!' },

  { id:'gfx1',  kw:['design','graphic','figma','ui','ux','illustrator','photoshop','branding','visual'],
    q:'What is the primary purpose of a wireframe in UI/UX design?',
    opts:['Plan layout and user flow before visual design','Create the final colored design','Write the HTML structure','Present to the client as final work'],
    correct:0,
    explain:'Wireframes map out layout and user flow cheaply, before investing in visual design. Low-fidelity wireframes (boxes and lines) let you validate structure quickly with stakeholders.' },

  { id:'gen1',  kw:[], // fallback
    q:'What\'s one skill you\'re most excited to develop from today\'s task?',
    opts:['Technical/coding skills','Problem-solving & debugging','Communication & teamwork','All of the above! 🚀'],
    correct:3,
    explain:'The best interns grow technically AND professionally. Every task at ProSensia teaches you to build, solve problems, and communicate — exactly the trifecta your career needs!' },
];

function pickQA(title, desc) {
  const h = (title + ' ' + (desc||'')).toLowerCase();
  for (const qa of QA_BANK) {
    if (qa.kw.length && qa.kw.some(k => h.includes(k))) return qa;
  }
  return QA_BANK[QA_BANK.length - 1];
}

// ===== DEBATE BANK =====
const DEBATE_BANK = [
  { kw:['iot','sensor','ac','geyser','maintenance','hvac','energy'], title:'Edge vs Cloud',
    a:{ label:'Edge Computing First', text:'Process sensor data ON the device — reduces latency, works offline, critical for real-time fault detection in AC systems.' },
    b:{ label:'Cloud-First AI', text:'Send all data to the cloud for powerful AI models and centralized management. Easier to update models across thousands of units.' },
    verdict:'Modern IoT does BOTH — edge for real-time alerts, cloud for deep AI pattern analysis. ProSensia\'s architecture uses exactly this hybrid approach!' },

  { kw:['ai','ml','model','neural','learning','predict'], title:'AI Speed vs Explainability',
    a:{ label:'Speed over Explainability', text:'Optimize for best performance. If the model predicts failures accurately in industrial IoT, that\'s all that matters.' },
    b:{ label:'Explainability is Critical', text:'Engineers must understand WHY the AI flagged a fault. Black-box AI in safety-critical systems can be dangerous without human oversight.' },
    verdict:'Explainability wins in safety-critical systems like HVAC. Engineers need to trust AND verify AI decisions before acting on them.' },

  { kw:['api','backend','database','sql','rest','code'], title:'Ship Fast vs Clean Code',
    a:{ label:'Ship Fast, Refactor Later', text:'Getting features to users quickly provides more value. Clean up code in the next sprint when requirements are clearer.' },
    b:{ label:'Clean Code From Day One', text:'Technical debt compounds quickly. Clean, documented code saves 10x time later and makes onboarding new team members much easier.' },
    verdict:'Balance matters — ship working code quickly, but maintain a baseline of quality. Never leave critical bugs or security issues for "later"!' },

  { kw:['security','cyber','hack','password','auth','vulnerability'], title:'Security vs Convenience',
    a:{ label:'Security First Always', text:'Never compromise on security for convenience. One breach can destroy user trust and expose sensitive data.' },
    b:{ label:'Balance Security and UX', text:'Overly complex security frustrates users. A system nobody can use securely is also a failure.' },
    verdict:'Security must be non-negotiable for critical systems, but security UX matters too. Make secure choices the easy choices!' },

  { kw:[], title:'Learning Style',
    a:{ label:'Learn by Reading Docs', text:'Documentation is ground truth. Deep reading builds strong mental models and good professional habits.' },
    b:{ label:'Learn by Building Things', text:'Hands-on projects reveal real problems. You learn fastest by building, breaking, and fixing yourself.' },
    verdict:'The best engineers do BOTH — read docs for fundamentals, build projects to solidify skills. At ProSensia, theory and hands-on work go hand in hand!' },
];

function pickDebate(title, desc) {
  const h = (title + ' ' + (desc||'')).toLowerCase();
  for (const d of DEBATE_BANK) {
    if (d.kw.length && d.kw.some(k => h.includes(k))) return d;
  }
  return DEBATE_BANK[DEBATE_BANK.length - 1];
}

// ===== UTILITY =====
function delay(ms) { return new Promise(r => setTimeout(r, ms)); }
function pad2(n)   { return String(n).padStart(2,'0'); }
function esc(s)    { return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

function _unlockHour() { return (typeof PS_UNLOCK_HOUR !== 'undefined') ? PS_UNLOCK_HOUR : 9; }
function _unlockMin()  { return (typeof PS_UNLOCK_MIN  !== 'undefined') ? PS_UNLOCK_MIN  : 0; }
function isUnlocked() {
  const now = new Date(), h = _unlockHour(), m = _unlockMin();
  return now.getHours() > h || (now.getHours() === h && now.getMinutes() >= m);
}
function getCountdown() {
  const now = new Date(), tgt = new Date(now);
  tgt.setHours(_unlockHour(), _unlockMin(), 0, 0);
  if (now >= tgt) return null;
  const d = tgt - now;
  return { h:Math.floor(d/3600000), m:Math.floor((d%3600000)/60000), s:Math.floor((d%60000)/1000),
           str:`${pad2(Math.floor(d/3600000))}:${pad2(Math.floor((d%3600000)/60000))}:${pad2(Math.floor((d%60000)/1000))}` };
}

// ===== CHARACTER ENGINE =====
class CharEngine {
  constructor(charKey) {
    this.def   = PS_CHARS[charKey] || PS_CHARS.ac;
    this.synth = window.speechSynthesis || null;
    this.voice = null;
    this.muted = localStorage.getItem('ps_voice_off') === '1';
    this._typing = false;
    this._avatarEl = null;
    this._textEl   = null;
    this._nameEl   = null;
    this._loadVoices();
  }

  _loadVoices() {
    if (!this.synth) return;
    const assign = () => {
      const vs = this.synth.getVoices();
      const female = /zira|cortana|karen|samantha|victoria|female|woman|girl/i;
      const male   = /david|james|mark|guy|male|man/i;
      const want   = this.def.voiceGender === 'female' ? female : male;
      this.voice = vs.find(v => v.lang.startsWith('en') && want.test(v.name))
                || vs.find(v => v.lang.startsWith('en-'))
                || vs[0] || null;
    };
    if (this.synth.onvoiceschanged !== undefined) this.synth.onvoiceschanged = assign;
    assign();
  }

  mount(avatarSel, textSel, nameSel) {
    this._avatarEl = document.querySelector(avatarSel);
    this._textEl   = document.querySelector(textSel);
    this._nameEl   = document.querySelector(nameSel);
    if (this._nameEl) this._nameEl.textContent = this.def.name;
    return this;
  }

  speak(text) {
    if (!this.synth || this.muted) return;
    this.synth.cancel();
    const utt = new SpeechSynthesisUtterance(text.replace(/[\u{1F300}-\u{1FAFF}]/gu, ''));
    if (this.voice) utt.voice = this.voice;
    utt.rate   = this.def.voiceGender === 'male' ? 0.88 : 1.06;
    utt.pitch  = this.def.voiceGender === 'male' ? 0.80 : 1.20;
    utt.volume = 0.85;
    if (this._avatarEl) {
      this._avatarEl.classList.add('talking');
      utt.onend = () => this._avatarEl && this._avatarEl.classList.remove('talking');
    }
    this.synth.speak(utt);
  }

  stopSpeech() {
    if (this.synth) this.synth.cancel();
    if (this._avatarEl) this._avatarEl.classList.remove('talking');
  }

  async type(text, speed) {
    if (!this._textEl) return;
    speed = speed || 20;
    this._typing = true;
    this._textEl.innerHTML = '';
    const cur = document.createElement('span');
    cur.className = 'type-cursor';
    this._textEl.appendChild(cur);
    for (const ch of text) {
      if (!this._typing) break;
      cur.insertAdjacentText('beforebegin', ch);
      await delay(speed);
    }
    cur.remove();
    this._typing = false;
  }

  stopTyping() {
    this._typing = false;
    const c = this._textEl && this._textEl.querySelector('.type-cursor');
    if (c) c.remove();
  }

  async say(text, opts) {
    opts = opts || {};
    this.stopTyping();
    if (!opts.silent) this.speak(text);
    await this.type(text, opts.speed || 20);
  }

  celebrate() {
    this.stopSpeech();
    if (!this._avatarEl) return;
    this._avatarEl.classList.remove('talking');
    this._avatarEl.classList.add('celebrating');
    setTimeout(() => this._avatarEl && this._avatarEl.classList.remove('celebrating'), 1600);
  }

  line(key) {
    const args = Array.prototype.slice.call(arguments, 1);
    const fn = this.def.lines[key];
    return typeof fn === 'function' ? fn.apply(null, args) : (fn || '');
  }

  rand(key) {
    const arr = this.def.lines[key];
    return Array.isArray(arr) ? arr[Math.floor(Math.random() * arr.length)] : (arr || '');
  }

  setMuted(val) {
    this.muted = val;
    localStorage.setItem('ps_voice_off', val ? '1' : '0');
    if (val) this.stopSpeech();
  }
}

// ===== WIZARD CONTROLLER =====
class WizardController {
  constructor(engine, tasks, internName, urls) {
    this.eng   = engine;
    this.tasks = tasks; // each task has ._localStatus set to task.status
    this.name  = internName;
    this.urls  = urls || {};
    this.idx   = 0;
  }

  async start() {
    const wrap = document.getElementById('wizard-wrap');
    if (wrap) wrap.style.display = '';
    this._buildProgress();
    await this.eng.say(this.eng.line('greeting', this.name));
    if (!this.tasks.length) {
      await delay(400);
      await this.eng.say(this.eng.line('noTasks', this.name));
      return;
    }
    await delay(500);
    await this._showTask(0);
  }

  async _showTask(idx) {
    this.idx = idx;
    this._updateProgress();
    const t = this.tasks[idx];

    // Character introduces task
    await this.eng.say(this.eng.line('taskIntro', this.name, t.title, t.est_minutes));

    // Render task detail
    const contentEl = document.getElementById('task-content');
    if (contentEl) contentEl.innerHTML = this._renderDetail(t);

    // Video prompt
    if (t.video_url) {
      await delay(500);
      await this.eng.say(this.eng.line('videoPrompt', t.title));
    }

    // Q&A + debate flow
    const qaEl = document.getElementById('qa-section');
    if (qaEl) {
      qaEl.style.display = '';
      await this._runQA(t);
    }
  }

  _renderDetail(t) {
    const sb = { pending:'b-muted', in_progress:'b-warning', done:'b-success' };
    const sl = { pending:'Pending', in_progress:'In Progress', done:'Done' };
    let h = '<div class="task-detail-card">';
    if (t.field || t.target_field) {
      h += `<div class="task-field-badge"><i class="bi bi-diagram-3 me-1"></i>${esc(t.field||t.target_field)}</div>`;
    }
    h += `<div class="d-flex justify-content-between align-items-start mb-2 flex-wrap gap-2">
            <h4 class="serif mb-0">${esc(t.title)}</h4>
            <span class="badge ${sb[t._localStatus]||'b-muted'}">${sl[t._localStatus]||t._localStatus}</span>
          </div>`;
    if (t.description) {
      h += `<p class="muted mb-3" style="font-size:14px;white-space:pre-wrap">${esc(t.description)}</p>`;
    }
    h += `<div class="muted" style="font-size:12px"><i class="bi bi-clock me-1"></i>${esc(String(t.est_minutes))} min estimated`;
    if (t.due_date) h += ` · <i class="bi bi-calendar3 me-1"></i>Due ${esc(t.due_date)}`;
    if (t.assigned_by_name) h += ` · <i class="bi bi-person me-1"></i>Assigned by ${esc(t.assigned_by_name)}`;
    h += '</div>';
    if (t.video_url) {
      h += `<a href="${esc(t.video_url)}" target="_blank" class="task-video-link">
              <i class="bi bi-play-circle-fill vi"></i>
              <div><div style="font-weight:600">Watch Resource</div>
              <div class="muted" style="font-size:12px">Opens in new tab — come back when done</div></div>
              <i class="bi bi-box-arrow-up-right ms-auto" style="font-size:14px;opacity:.45"></i>
            </a>`;
    }
    h += '</div>';
    return h;
  }

  _runQA(task) {
    const self = this;
    return new Promise(function(outerResolve) {
      const qa = pickQA(task.title, task.description);
      const qaEl = document.getElementById('qa-section');
      qaEl.innerHTML = `
        <div class="qa-section">
          <div class="qa-question"><i class="bi bi-question-circle me-2" style="color:var(--primary)"></i>${esc(qa.q)}</div>
          <div class="qa-opts" id="qa-opts-inner">
            ${qa.opts.map(function(o,i){ return `<button class="qa-opt-btn" data-i="${i}">${esc(o)}</button>`; }).join('')}
          </div>
          <div id="qa-explain-box" style="display:none"></div>
        </div>`;

      qaEl.querySelectorAll('.qa-opt-btn').forEach(function(btn) {
        btn.addEventListener('click', async function() {
          if (btn.disabled) return;
          const chosen = parseInt(btn.dataset.i);
          qaEl.querySelectorAll('.qa-opt-btn').forEach(function(b){ b.disabled = true; });
          const ok = chosen === qa.correct;
          btn.classList.add(ok ? 'correct' : 'wrong');
          if (!ok) {
            const cb = qaEl.querySelector(`[data-i="${qa.correct}"]`);
            if (cb) cb.classList.add('correct');
          }
          const expEl = document.getElementById('qa-explain-box');
          expEl.style.display = '';
          expEl.className = 'qa-explain ' + (ok ? 'good' : 'hint');
          const fb = self.eng.rand(ok ? 'correct' : 'wrong');
          expEl.innerHTML = `<strong>${esc(fb)}</strong> ${esc(qa.explain)}`;
          await self.eng.say(fb + ' ' + qa.explain, { speed: 16 });
          await delay(500);

          // Debate
          await self._runDebate(task, pickDebate(task.title, task.description), outerResolve);
        });
      });
    });
  }

  _runDebate(task, debate, outerResolve) {
    const self = this;
    return new Promise(async function(res) {
      const qaEl = document.getElementById('qa-section');
      const debEl = document.createElement('div');
      debEl.className = 'debate-section';
      debEl.innerHTML = `
        <div class="debate-title"><i class="bi bi-chat-square-quote me-1"></i>Quick Debate · ${esc(debate.title)}</div>
        <p class="muted mb-3" style="font-size:13px">${esc(self.eng.line('debate'))}</p>
        <div class="debate-sides">
          <div class="debate-card side-a" data-side="a">
            <div class="side-label">Side A</div>
            <strong>${esc(debate.a.label)}</strong><br>
            <span class="muted" style="font-size:12px">${esc(debate.a.text)}</span>
          </div>
          <div class="debate-card side-b" data-side="b">
            <div class="side-label">Side B</div>
            <strong>${esc(debate.b.label)}</strong><br>
            <span class="muted" style="font-size:12px">${esc(debate.b.text)}</span>
          </div>
        </div>
        <div id="debate-verdict-box" style="display:none" class="debate-verdict"></div>`;
      qaEl.appendChild(debEl);
      await self.eng.say(self.eng.line('debate'), { silent: true });

      debEl.querySelectorAll('.debate-card').forEach(function(card) {
        card.addEventListener('click', async function() {
          debEl.querySelectorAll('.debate-card').forEach(function(c){ c.classList.remove('chosen'); });
          card.classList.add('chosen');
          const vEl = document.getElementById('debate-verdict-box');
          vEl.style.display = '';
          vEl.innerHTML = `<i class="bi bi-lightbulb me-2" style="color:var(--primary)"></i><strong>ProSensia View:</strong> ${esc(debate.verdict)}`;
          await self.eng.say(debate.verdict, { speed: 18 });
          await delay(500);
          self._showCompleteBtn(task, outerResolve);
          res();
        });
      });
    });
  }

  _showCompleteBtn(task, resolve) {
    const actEl = document.getElementById('task-actions');
    if (!actEl) return;
    const alreadyDone = task._localStatus === 'done';
    const isLast = this.idx === this.tasks.length - 1;
    actEl.innerHTML = `<div class="wiz-actions">
      ${!alreadyDone
        ? `<button class="btn btn-primary btn-lg" id="btn-complete-task"><i class="bi bi-check2-circle me-2"></i>Mark as Complete</button>`
        : `<span class="badge b-success p-2 fs-6"><i class="bi bi-check-circle me-1"></i>Already completed</span>`}
      ${!isLast
        ? `<button class="btn btn-ghost" id="btn-next-task">Next task <i class="bi bi-arrow-right ms-1"></i></button>`
        : ''}
    </div>`;

    const self = this;
    const btnC = document.getElementById('btn-complete-task');
    if (btnC) {
      btnC.addEventListener('click', async function() {
        btnC.disabled = true;
        btnC.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving…';
        await self._markDone(task);
        self.eng.celebrate();
        const celebMsg = self.eng.line('complete', self.name, self._allDone());
        await self.eng.say(celebMsg);
        await delay(700);
        if (self._allDone()) { self._showCompletion(); } else { await self._showTask(self.idx + 1); }
        if (resolve) resolve();
      });
    }
    const btnN = document.getElementById('btn-next-task');
    if (btnN) {
      btnN.addEventListener('click', async function() {
        await self._showTask(self.idx + 1);
        if (resolve) resolve();
      });
    }
  }

  async _markDone(task) {
    task._localStatus = 'done';
    this._updateProgress();
    try {
      const fd = new FormData();
      fd.append('action','set_status'); fd.append('id', task.id); fd.append('status','done');
      await fetch(this.urls.tasks || location.href, {
        method:'POST', body:fd, headers:{'X-Requested-With':'XMLHttpRequest'}
      });
    } catch(e) { /* silent fail */ }
  }

  _allDone() { return this.tasks.every(function(t){ return t._localStatus === 'done'; }); }

  _showCompletion() {
    const wrap = document.getElementById('wizard-wrap');
    if (!wrap) return;
    wrap.innerHTML = `
      <div class="completion-screen glass card-pad">
        <span class="trophy-row">🏆🎉⭐</span>
        <h2 class="serif mt-3" style="font-size:32px">All Tasks Done!</h2>
        <p class="muted">You've completed all tasks for today. Incredible work, ${esc(this.name)}!</p>
        <p class="muted" style="font-size:12px">Your mentor &amp; management have been notified. 🌟</p>
        <div class="d-flex gap-3 justify-content-center flex-wrap mt-4">
          <a href="${esc(this.urls.socialPost||'#')}" class="btn btn-primary">
            <i class="bi bi-linkedin me-2"></i>Share on LinkedIn
          </a>
          <a href="${esc(this.urls.attendance||'#')}" class="btn btn-outline-light">
            <i class="bi bi-box-arrow-right me-2"></i>Check Out &amp; Go
          </a>
        </div>
      </div>`;
    this.eng.celebrate();
    this.eng.say(this.eng.line('linkedin', this.name));
    this._launchConfetti();
  }

  _launchConfetti() {
    const colors = ['#d4a84c','#29b6f6','#ff7043','#34d399','#a78bfa'];
    for (let i = 0; i < 32; i++) {
      const el = document.createElement('div');
      el.className = 'confetti-piece';
      el.style.cssText = `
        left:${Math.random()*100}vw;
        top:${Math.random()*40}vh;
        background:${colors[i%colors.length]};
        animation-delay:${Math.random()*0.8}s;
        animation-duration:${1.4+Math.random()*0.8}s;
        width:${6+Math.random()*8}px;height:${6+Math.random()*8}px;
        border-radius:${Math.random()>0.5?'50%':'2px'};
      `;
      document.body.appendChild(el);
      setTimeout(function(){ el.remove(); }, 3000);
    }
  }

  _buildProgress() {
    const el = document.getElementById('wiz-progress');
    if (!el || !this.tasks.length) return;
    let h = '';
    this.tasks.forEach(function(t, i) {
      if (i > 0) h += `<div class="wiz-line" id="wl-${i}"></div>`;
      h += `<div class="wiz-dot" id="wd-${i}" title="${esc(t.title)}"></div>`;
    });
    el.innerHTML = h;
  }

  _updateProgress() {
    const self = this;
    this.tasks.forEach(function(t, i) {
      const dot = document.getElementById('wd-' + i);
      if (!dot) return;
      dot.className = 'wiz-dot' +
        (t._localStatus === 'done' ? ' done' : (i === self.idx ? ' active' : ''));
      if (i > 0) {
        const line = document.getElementById('wl-' + i);
        if (line) line.className = 'wiz-line' + (self.tasks[i-1]._localStatus === 'done' ? ' done' : '');
      }
    });
  }
}
