import { useEffect, useMemo, useState, type ReactNode } from "react";
import {
  AlertCircle,
  ArrowLeft,
  Bell,
  BookOpen,
  CalendarDays,
  Camera,
  Check,
  CheckCircle2,
  ChevronDown,
  ChevronRight,
  CircleDollarSign,
  Clock3,
  Download,
  Eye,
  FileText,
  GraduationCap,
  HelpCircle,
  Home,
  Image,
  Info,
  LockKeyhole,
  LogOut,
  Mail,
  MapPin,
  Menu,
  MessageSquareText,
  Moon,
  MoreHorizontal,
  Phone,
  RefreshCw,
  Search,
  Settings,
  ShieldCheck,
  Sparkles,
  Star,
  Sun,
  Upload,
  User,
  UserRound,
  WifiOff,
  X,
  Zap,
} from "lucide-react";

type Screen =
  | "splash"
  | "login"
  | "profileSetup"
  | "home"
  | "subjects"
  | "subject"
  | "grades"
  | "attendance"
  | "schedule"
  | "announcements"
  | "announcement"
  | "enrollment"
  | "camera"
  | "payments"
  | "profile";

const screens: Screen[] = [
  "splash",
  "login",
  "profileSetup",
  "home",
  "subjects",
  "subject",
  "grades",
  "attendance",
  "schedule",
  "announcements",
  "announcement",
  "enrollment",
  "camera",
  "payments",
  "profile",
];

const announcementImage =
  "https://images.unsplash.com/photo-1698795635889-ed23cf3877d6?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&q=82&w=1080";

const subjects = [
  ["Matemática", "Prof. Marco Suárez", 87],
  ["Lengua Castellana", "Prof. Ana Rodríguez", 76],
  ["Ciencias Sociales", "Prof. Carolina Rojas", 64],
  ["Ciencias Naturales", "Prof. Luis Fernández", 51],
  ["Inglés", "Prof. Mónica Flores", 42],
  ["Educación Física", "Prof. Diego Cruz", 92],
  ["Artes Plásticas", "Prof. Elena Vargas", 70],
  ["Informática", "Prof. Pablo Méndez", 58],
  ["Religión", "Prof. José Aramayo", 81],
] as const;

const tabs: { id: Screen; label: string; icon: typeof Home }[] = [
  { id: "home", label: "Inicio", icon: Home },
  { id: "grades", label: "Notas", icon: BookOpen },
  { id: "attendance", label: "Asistencia", icon: CalendarDays },
  { id: "announcements", label: "Comunicados", icon: MessageSquareText },
  { id: "profile", label: "Perfil", icon: UserRound },
];

function Crest({ inverse = false }: { inverse?: boolean }) {
  return (
    <div
      className={`crest ${inverse ? "crest-inverse" : ""}`}
      aria-label="Escudo Colegio San Bernardo"
    >
      <GraduationCap size={27} strokeWidth={1.8} />
      <span>SB</span>
    </div>
  );
}

function Button({
  children,
  kind = "primary",
  icon,
  onClick,
  disabled = false,
}: {
  children: ReactNode;
  kind?: "primary" | "secondary" | "ghost" | "danger";
  icon?: ReactNode;
  onClick?: () => void;
  disabled?: boolean;
}) {
  return (
    <button disabled={disabled} onClick={onClick} className={`btn btn-${kind}`}>
      {icon}
      {children}
    </button>
  );
}

function AppBar({
  title,
  subtitle,
  onBack,
  action,
}: {
  title: string;
  subtitle?: string;
  onBack?: () => void;
  action?: ReactNode;
}) {
  return (
    <header className="appbar">
      {onBack ? (
        <button className="icon-btn" onClick={onBack} aria-label="Volver">
          <ArrowLeft />
        </button>
      ) : (
        <Crest />
      )}
      <div className="appbar-copy">
        <strong>{title}</strong>
        {subtitle && <span>{subtitle}</span>}
      </div>
      <div className="appbar-action">{action || <span />}</div>
    </header>
  );
}

function TabBar({ active, go }: { active: Screen; go: (s: Screen) => void }) {
  return (
    <nav className="tabbar">
      {tabs.map(({ id, label, icon: Icon }) => (
        <button key={id} onClick={() => go(id)} className={active === id ? "active" : ""}>
          <Icon size={21} strokeWidth={active === id ? 2.3 : 1.8} />
          <span>{label}</span>
        </button>
      ))}
    </nav>
  );
}

function Page({ children, className = "" }: { children: ReactNode; className?: string }) {
  return <main className={`page ${className}`}>{children}</main>;
}

function SectionTitle({ children, action }: { children: ReactNode; action?: ReactNode }) {
  return (
    <div className="section-title">
      <h2>{children}</h2>
      {action}
    </div>
  );
}

function Score({ value }: { value: number }) {
  const tone = value >= 85 ? "excellent" : value >= 69 ? "good" : value >= 51 ? "warn" : "danger";
  return <span className={`score ${tone}`}>{value}</span>;
}

function Chip({ children, tone = "neutral" }: { children: ReactNode; tone?: string }) {
  return <span className={`chip chip-${tone}`}>{children}</span>;
}

function Splash() {
  return (
    <div className="splash">
      <div className="splash-mark">
        <Crest inverse />
        <h1>CSB 360</h1>
        <p>Colegio San Bernardo</p>
      </div>
      <div className="loading-copy">
        <span className="spinner" />
        Verificando sesión…
      </div>
      <small>Versión 1.0.0</small>
    </div>
  );
}

function Login({ onLogin, onDemo }: { onLogin: () => void; onDemo: () => void }) {
  const [demo, setDemo] = useState(false);
  const [error, setError] = useState(false);
  return (
    <Page className="login">
      <div className="login-brand">
        <Crest />
        <span>CSB 360</span>
      </div>
      <div className="login-hero">
        <span className="eyebrow">PORTAL DEL ESTUDIANTE</span>
        <h1>
          Bienvenida a tu
          <br />
          espacio escolar
        </h1>
        <p>Consulta tus avances, horarios y novedades del colegio en un solo lugar.</p>
      </div>
      {error && (
        <div className="alert danger">
          <AlertCircle />
          <span>
            <strong>Cuenta no registrada</strong>Comunícate con Secretaría para solicitar acceso.
          </span>
          <button onClick={() => setError(false)}>
            <X />
          </button>
        </div>
      )}
      <div className="login-actions">
        <button className="google-btn" onClick={onLogin}>
          <span className="google-g">G</span>Continuar con Google
        </button>
        <p>
          Solo cuentas <strong>@cosabe.edu.bo</strong>
        </p>
        <button className="text-link" onClick={() => setError(true)}>
          ¿Problemas para ingresar?
        </button>
      </div>
      <div className="demo">
        <button onClick={() => setDemo(!demo)}>
          <span>
            <Zap /> Acceso de prueba
          </span>
          <ChevronDown className={demo ? "rotate" : ""} />
        </button>
        {demo && (
          <div className="demo-body">
            <code>valeria@cosabe.edu.bo</code>
            <code>CSB2025</code>
            <Button onClick={onDemo} kind="secondary">
              Ingresar a la demo
            </Button>
          </div>
        )}
      </div>
    </Page>
  );
}

function ProfileSetup({ done }: { done: () => void }) {
  return (
    <>
      <AppBar title="Completa tus datos" onBack={done} />
      <Page>
        <div className="setup-intro">
          <div className="avatar avatar-lg">
            VQ
            <button>
              <Camera size={16} />
            </button>
          </div>
          <div>
            <h1>Hola, Valeria</h1>
            <p>Confirma tus datos para comenzar.</p>
          </div>
        </div>
        <div className="readonly card">
          <div>
            <span>Nombre completo</span>
            <strong>Valeria Quispe Mamani</strong>
          </div>
          <div className="two-col">
            <div>
              <span>Curso</span>
              <strong>4º Sec. “B”</strong>
            </div>
            <div>
              <span>Código</span>
              <strong>8640</strong>
            </div>
          </div>
        </div>
        <label className="field">
          <span>Teléfono</span>
          <div>
            <Phone />
            <input defaultValue="729 184 26" />
          </div>
        </label>
        <label className="field">
          <span>Dirección</span>
          <div>
            <MapPin />
            <input defaultValue="Barrio San Jorge, Tarija" />
          </div>
        </label>
        <label className="check-row">
          <input type="checkbox" defaultChecked />
          <span>Acepto el tratamiento de mis datos personales y conozco mis derechos ARCO.</span>
        </label>
        <Button onClick={done}>Guardar y continuar</Button>
      </Page>
    </>
  );
}

function HomeScreen({ go }: { go: (s: Screen) => void }) {
  return (
    <>
      <Page className="home-page">
        <header className="home-head">
          <div>
            <span>Buenos días,</span>
            <h1>Hola, Valeria</h1>
            <Chip>4º Sec. · Paralelo B</Chip>
          </div>
          <button className="bell">
            <Bell />
            <b>3</b>
          </button>
        </header>
        <button className="enroll-banner" onClick={() => go("enrollment")}>
          <span className="banner-icon">
            <FileText />
          </span>
          <span>
            <small>INSCRIPCIÓN 2026</small>
            <strong>Completa tu inscripción</strong>
            <em>2 de 5 documentos listos</em>
          </span>
          <ChevronRight />
        </button>
        <section className="performance-card">
          <div>
            <span className="eyebrow light">RENDIMIENTO GENERAL</span>
            <h2>Vas por buen camino</h2>
            <p>Subiste 4 puntos este trimestre</p>
            <span className="trend">
              <Sparkles /> Buen rendimiento
            </span>
          </div>
          <div className="progress-ring">
            <strong>82</strong>
            <span>/100</span>
          </div>
        </section>
        <div className="stats">
          <button
            type="button"
            aria-label="Ver detalle de asistencia"
            onClick={() => go("attendance")}
          >
            <span>94%</span>
            <small>Asistencia</small>
          </button>
          <button type="button" aria-label="Ver mis 9 materias" onClick={() => go("subjects")}>
            <span>9</span>
            <small>Materias</small>
          </button>
          <button
            type="button"
            aria-label="Ver notas del segundo trimestre"
            onClick={() => go("grades")}
          >
            <span>2do</span>
            <small>Trimestre</small>
          </button>
        </div>
        <SectionTitle action={<button onClick={() => go("schedule")}>Ver horario</button>}>
          Ahora
        </SectionTitle>
        <button className="now-card" onClick={() => go("schedule")}>
          <span className="now-time">
            <strong>09:20</strong>
            <em>10:05</em>
          </span>
          <span className="now-line" />
          <span className="now-info">
            <small>EN CURSO · FALTAN 24 MIN</small>
            <strong>Matemática</strong>
            <em>Prof. Marco Suárez · Aula 12</em>
          </span>
          <ChevronRight />
        </button>
        <SectionTitle>Accesos rápidos</SectionTitle>
        <div className="quick-grid">
          {[
            ["Notas", BookOpen, "grades"],
            ["Asistencia", CalendarDays, "attendance"],
            ["Pagos", CircleDollarSign, "payments"],
            ["Inscripción", FileText, "enrollment"],
          ].map(([label, Icon, id]) => (
            <button key={label as string} onClick={() => go(id as Screen)}>
              <span>
                <Icon />
              </span>
              {label as string}
            </button>
          ))}
        </div>
        <SectionTitle action={<button onClick={() => go("announcements")}>Ver todos</button>}>
          Comunicados
        </SectionTitle>
        <button className="news-card" onClick={() => go("announcement")}>
          <img src={announcementImage} alt="Estudiantes en una actividad escolar" />
          <span>
            <small>
              <b>NUEVO</b> · 08/04/2025
            </small>
            <strong>Feria científica y tecnológica 2025</strong>
            <em>Conoce las bases y fechas para participar.</em>
          </span>
          <Star className="star" size={18} fill="currentColor" />
        </button>
      </Page>
    </>
  );
}

function Subjects({ go, back }: { go: (s: Screen) => void; back: () => void }) {
  const [query, setQuery] = useState("");
  const filtered = subjects.filter((s) => s[0].toLowerCase().includes(query.toLowerCase()));
  return (
    <>
      <AppBar title="Mis materias" subtitle="2do Trimestre" onBack={back} />
      <Page>
        <label className="search">
          <Search />
          <input
            placeholder="Buscar materia"
            value={query}
            onChange={(e) => setQuery(e.target.value)}
          />
        </label>
        <div className="segmented">
          <button>1T</button>
          <button className="active">2T</button>
          <button>3T</button>
        </div>
        <p className="list-caption">9 MATERIAS · PROMEDIO 82</p>
        <div className="subject-list">
          {filtered.map(([name, teacher, score]) => (
            <button key={name} onClick={() => go("subject")}>
              <span className="subject-initial">{name[0]}</span>
              <span className="subject-copy">
                <strong>{name}</strong>
                <small>{teacher}</small>
              </span>
              <Score value={score} />
              <ChevronRight />
            </button>
          ))}
        </div>
      </Page>
    </>
  );
}

function SubjectDetail({ back }: { back: () => void }) {
  const [tab, setTab] = useState<"Resumen" | "Notas" | "Asistencia">("Resumen");
  return (
    <>
      <AppBar
        title="Matemática"
        subtitle="Prof. Marco Suárez"
        onBack={back}
        action={
          <button className="icon-btn">
            <MoreHorizontal />
          </button>
        }
      />
      <div className="top-tabs">
        {(["Resumen", "Notas", "Asistencia"] as const).map((t) => (
          <button className={tab === t ? "active" : ""} onClick={() => setTab(t)}>
            {t}
          </button>
        ))}
      </div>
      <Page>
        {tab === "Resumen" && (
          <>
            <div className="score-hero">
              <span>
                <small>PROMEDIO ACTUAL</small>
                <strong>87</strong>
                <em>Desarrollo pleno</em>
              </span>
              <div className="score-donut">DP</div>
            </div>
            <div className="scale">
              <span className="danger">
                ED<small>0–50</small>
              </span>
              <span className="warn">
                DA<small>51–68</small>
              </span>
              <span className="good">
                DO<small>69–84</small>
              </span>
              <span className="excellent active">
                DP<small>85–100</small>
              </span>
            </div>
            <div className="two-cards">
              <div className="card metric">
                <CalendarDays />
                <span>
                  <strong>96%</strong>
                  <small>Asistencia</small>
                </span>
              </div>
              <div className="card metric">
                <Clock3 />
                <span>
                  <strong>Lun. y Mié.</strong>
                  <small>08:35–10:05</small>
                </span>
              </div>
            </div>
            <SectionTitle>Próxima clase</SectionTitle>
            <div className="card next-class">
              <span>
                MIÉ
                <br />
                <strong>09</strong>
                <br />
                ABR
              </span>
              <div>
                <strong>Matemática</strong>
                <small>08:35–10:05 · Aula 12</small>
              </div>
            </div>
          </>
        )}
        {tab === "Notas" && (
          <>
            <div className="criteria">
              <div>
                <span>SE</span>
                <strong>9</strong>
                <small>/ 10</small>
              </div>
              <div>
                <span>SA</span>
                <strong>39</strong>
                <small>/ 45</small>
              </div>
              <div>
                <span>HA</span>
                <strong>35</strong>
                <small>/ 40</small>
              </div>
              <div>
                <span>AU</span>
                <strong>4</strong>
                <small>/ 5</small>
              </div>
            </div>
            <SectionTitle>Actividades evaluadas</SectionTitle>
            {[
              ["Examen de álgebra", "35% · 02/04/2025", "91"],
              ["Trabajo práctico #3", "25% · 28/03/2025", "84"],
              ["Participación y carpeta", "20% · 21/03/2025", "88"],
            ].map((x) => (
              <div className="grade-row">
                <span>
                  <strong>{x[0]}</strong>
                  <small>{x[1]}</small>
                </span>
                <b>{x[2]}</b>
              </div>
            ))}
            <div className="empty-inline">
              <Info />
              <span>
                <strong>Todo al día</strong>Aún quedan actividades por calificar.
              </span>
            </div>
          </>
        )}
        {tab === "Asistencia" && (
          <>
            <div className="attendance-big">
              <strong>96%</strong>
              <span>Asistencia en Matemática</span>
            </div>
            {["02/04/2025", "31/03/2025", "26/03/2025", "24/03/2025"].map((d, i) => (
              <div className="record">
                <span>
                  <strong>{d}</strong>
                  <small>08:35 · Aula 12</small>
                </span>
                <Chip tone={i === 2 ? "late" : "present"}>{i === 2 ? "Retraso" : "Presente"}</Chip>
              </div>
            ))}
          </>
        )}
      </Page>
    </>
  );
}

function Grades() {
  const [term, setTerm] = useState(2);
  return (
    <>
      <AppBar
        title="Notas"
        subtitle="Gestión 2025"
        action={
          <button className="icon-btn">
            <Download />
          </button>
        }
      />
      <Page>
        <div className="segmented term">
          <button onClick={() => setTerm(1)} className={term === 1 ? "active" : ""}>
            1er
          </button>
          <button onClick={() => setTerm(2)} className={term === 2 ? "active" : ""}>
            2do
          </button>
          <button onClick={() => setTerm(3)} className={term === 3 ? "active" : ""}>
            3er
          </button>
        </div>
        {term === 3 ? (
          <div className="locked-state">
            <span>
              <LockKeyhole />
            </span>
            <h2>Aún no están publicadas</h2>
            <p>Las notas del 3er trimestre se publican al cierre.</p>
            <small>Fecha estimada: 28/11/2025</small>
          </div>
        ) : (
          <>
            <div className="average-card">
              <span>
                <small>PROMEDIO GENERAL</small>
                <strong>{term === 2 ? "82" : "79"}</strong>
                <em>Desarrollo óptimo</em>
              </span>
              <span className="delta">↑ 4 pts</span>
            </div>
            <div className="grade-list">
              {subjects.map(([name, , score]) => (
                <div>
                  <span>
                    <strong>{name}</strong>
                    <Chip
                      tone={
                        score >= 85
                          ? "present"
                          : score >= 69
                            ? "info"
                            : score >= 51
                              ? "late"
                              : "absent"
                      }
                    >
                      {score >= 85 ? "DP" : score >= 69 ? "DO" : score >= 51 ? "DA" : "ED"}
                    </Chip>
                  </span>
                  <Score value={score} />
                </div>
              ))}
            </div>
            <div className="legend">
              <span>
                <b className="dot red" />
                ED 0–50
              </span>
              <span>
                <b className="dot amber" />
                DA 51–68
              </span>
              <span>
                <b className="dot blue" />
                DO 69–84
              </span>
              <span>
                <b className="dot green" />
                DP 85–100
              </span>
            </div>
            <Button icon={<Download />}>Descargar boletín PDF</Button>
            <p className="official">
              <ShieldCheck /> Documento oficial SIE
            </p>
          </>
        )}
      </Page>
    </>
  );
}

function Attendance() {
  const [selected, setSelected] = useState<number | null>(null);
  const days = Array.from({ length: 30 }, (_, i) => i + 1);
  return (
    <>
      <AppBar
        title="Asistencia"
        subtitle="Abril 2025"
        action={
          <button className="icon-btn">
            <Download />
          </button>
        }
      />
      <Page>
        <div className="attendance-summary">
          <div className="attendance-ring">
            <strong>94%</strong>
          </div>
          <div>
            <small>ASISTENCIA GENERAL</small>
            <strong>Muy buena asistencia</strong>
            <span>Solo 2 faltas este trimestre</span>
          </div>
        </div>
        <div className="attendance-counts">
          {[
            ["24", "Presente", "green"],
            ["2", "Ausente", "red"],
            ["1", "Retraso", "amber"],
            ["1", "Licencia", "blue"],
            ["1", "Retiro just.", "slate"],
          ].map((x) => (
            <div>
              <b className={`dot ${x[2]}`} />
              <strong>{x[0]}</strong>
              <small>{x[1]}</small>
            </div>
          ))}
        </div>
        <div className="calendar">
          <div className="calendar-head">
            <button>
              <ChevronRight className="flip" />
            </button>
            <strong>Abril 2025</strong>
            <button>
              <ChevronRight />
            </button>
          </div>
          <div className="week">
            {["L", "M", "M", "J", "V", "S", "D"].map((x) => (
              <span>{x}</span>
            ))}
          </div>
          <div className="days">
            <i />
            <span className="muted">1</span>
            {days.slice(1).map((d) => (
              <button
                onClick={() => setSelected(d)}
                className={`${selected === d ? "selected" : ""} ${d === 8 ? "today" : ""}`}
              >
                {d}
                <b
                  className={`day-dot ${d === 3 || d === 17 ? "abs" : d === 11 ? "late" : d === 22 ? "leave" : ""}`}
                />
              </button>
            ))}
          </div>
        </div>
        <SectionTitle>Últimos registros</SectionTitle>
        {[
          ["08/04/2025", "Matemática", "Presente", "present"],
          ["07/04/2025", "Inglés", "Retraso", "late"],
          ["04/04/2025", "Ciencias Naturales", "Licencia", "info"],
        ].map((x) => (
          <div className="record">
            <span>
              <strong>{x[0]}</strong>
              <small>{x[1]}</small>
            </span>
            <Chip tone={x[3]}>{x[2]}</Chip>
          </div>
        ))}
      </Page>
      {selected && (
        <>
          <div className="overlay" onClick={() => setSelected(null)} />
          <div className="bottom-sheet">
            <i />
            <div className="sheet-head">
              <span>
                <small>DETALLE DEL DÍA</small>
                <h2>{selected} de abril</h2>
              </span>
              <button onClick={() => setSelected(null)}>
                <X />
              </button>
            </div>
            <div className="record">
              <span>
                <strong>Matemática</strong>
                <small>08:35 · Prof. Marco Suárez</small>
              </span>
              <Chip tone="present">Presente</Chip>
            </div>
            <div className="record">
              <span>
                <strong>Lengua Castellana</strong>
                <small>10:20 · Prof. Ana Rodríguez</small>
              </span>
              <Chip tone="present">Presente</Chip>
            </div>
            <Button onClick={() => setSelected(null)}>Entendido</Button>
          </div>
        </>
      )}
    </>
  );
}

function Schedule({ back }: { back: () => void }) {
  return (
    <>
      <AppBar title="Mi horario" subtitle="4º Secundaria “B”" onBack={back} />
      <Page>
        <div className="day-selector">
          {["Lun", "Mar", "Mié", "Jue", "Vie"].map((d, i) => (
            <button className={i === 1 ? "active" : ""}>
              <span>{d}</span>
              <b>{7 + i}</b>
            </button>
          ))}
        </div>
        <div className="timeline">
          {[
            ["07:45", "08:30", "Lengua Castellana", "Prof. Ana Rodríguez", "Aula 8", false],
            ["08:35", "09:20", "Ciencias Sociales", "Prof. Carolina Rojas", "Aula 8", false],
            ["09:20", "10:05", "Matemática", "Prof. Marco Suárez", "Aula 12", true],
            ["10:05", "10:20", "RECREO", "", "Patio central", false],
            ["10:20", "11:05", "Ciencias Naturales", "Prof. Luis Fernández", "Laboratorio", false],
            ["11:10", "11:55", "Informática", "Prof. Pablo Méndez", "Sala 2", false],
          ].map((x) => (
            <div className={`timeline-row ${x[5] ? "current" : ""}`}>
              <span className="time">
                <strong>{x[0]}</strong>
                <small>{x[1]}</small>
              </span>
              <i />
              <div>
                <small>
                  {x[5] && "AHORA · "}
                  {x[2]}
                </small>
                {x[3] && <strong>{x[3]}</strong>}
                <span>
                  <MapPin /> {x[4]}
                </span>
              </div>
            </div>
          ))}
        </div>
      </Page>
    </>
  );
}

function Announcements({ go }: { go: (s: Screen) => void }) {
  const [filter, setFilter] = useState("Todos");
  const empty = filter === "Destacados";
  return (
    <>
      <AppBar
        title="Comunicados"
        subtitle="3 sin leer"
        action={
          <button className="icon-btn">
            <Search />
          </button>
        }
      />
      <div className="top-tabs">
        {["Todos", "Sin leer", "Destacados"].map((x) => (
          <button onClick={() => setFilter(x)} className={filter === x ? "active" : ""}>
            {x}
          </button>
        ))}
      </div>
      <Page>
        {empty ? (
          <div className="empty-state">
            <span>
              <Star />
            </span>
            <h2>No tienes destacados</h2>
            <p>Guarda comunicados importantes para encontrarlos aquí.</p>
            <Button kind="secondary" onClick={() => setFilter("Todos")}>
              Ver todos
            </Button>
          </div>
        ) : (
          <div className="news-list">
            <button className="feature-news" onClick={() => go("announcement")}>
              <img src={announcementImage} alt="Estudiantes trabajando juntos" />
              <span className="image-overlay" />
              <div>
                <Chip tone="present">NUEVO</Chip>
                <h2>Feria científica y tecnológica 2025</h2>
                <p>Inscripciones abiertas hasta el 25 de abril.</p>
                <small>Dirección · 08/04/2025</small>
              </div>
              <Star fill="white" />
            </button>
            {[
              ["Reunión informativa de padres", "Dirección · 05/04/2025", true],
              ["Cronograma de evaluaciones — 2do trimestre", "Coordinación · 02/04/2025", true],
              ["Campaña solidaria de invierno", "Pastoral · 28/03/2025", false],
            ].map((x) => (
              <button className="simple-news" onClick={() => go("announcement")}>
                <span className="news-icon">
                  <Mail />
                </span>
                <span>
                  <small>
                    {x[2] && <b>NUEVO</b>} {x[1]}
                  </small>
                  <strong>{x[0]}</strong>
                  <em>Información importante para estudiantes y familias.</em>
                </span>
                <ChevronRight />
              </button>
            ))}
          </div>
        )}
      </Page>
    </>
  );
}

function Announcement({ back }: { back: () => void }) {
  const [read, setRead] = useState(false);
  return (
    <>
      <AppBar
        title="Comunicado"
        onBack={back}
        action={
          <button className="icon-btn">
            <Star />
          </button>
        }
      />
      <Page className="article">
        <img src={announcementImage} alt="Estudiantes participando de una actividad académica" />
        <Chip tone="present">NUEVO</Chip>
        <h1>Feria científica y tecnológica 2025</h1>
        <div className="byline">
          <span className="avatar">D</span>
          <span>
            <strong>Dirección Académica</strong>
            <small>08/04/2025 · 09:30</small>
          </span>
        </div>
        <p>Estimada comunidad estudiantil:</p>
        <p>
          Invitamos a todos los cursos de secundaria a participar de nuestra Feria Científica y
          Tecnológica 2025, un espacio para compartir ideas, investigar y proponer soluciones para
          nuestra comunidad.
        </p>
        <h3>Fechas importantes</h3>
        <ul>
          <li>Inscripción de proyectos: hasta el 25/04/2025</li>
          <li>Presentación de propuestas: 09/05/2025</li>
          <li>Feria abierta: 23/05/2025</li>
        </ul>
        <div className="attachment">
          <FileText />
          <span>
            <strong>Bases de la feria 2025.pdf</strong>
            <small>PDF · 1,8 MB</small>
          </span>
          <button>
            <Download />
          </button>
        </div>
        <Button
          onClick={() => setRead(true)}
          kind={read ? "secondary" : "primary"}
          icon={read ? <Check /> : <Eye />}
        >
          {read ? "Comunicado leído" : "Marcar como leído"}
        </Button>
      </Page>
    </>
  );
}

function Stepper({ step }: { step: number }) {
  return (
    <div className="stepper">
      {["Datos", "Documentos", "Domicilio", "Contrato", "Confirmar"].map((x, i) => (
        <div className={i + 1 <= step ? "done" : ""}>
          <span>{i + 1 < step ? <Check /> : i + 1}</span>
          <small>{x}</small>
        </div>
      ))}
    </div>
  );
}

function Enrollment({ back, camera }: { back: () => void; camera: () => void }) {
  const [step, setStep] = useState(2);
  const docs = [
    ["CI — anverso", "Entregado", "ok"],
    ["CI — reverso", "La foto está borrosa, vuelve a intentarlo", "bad"],
    ["Certificado de nacimiento", "Entregado", "ok"],
    ["CI de padre, madre o tutor", "Pendiente", "pending"],
    ["Comprobante de pago", "Pendiente", "pending"],
  ];
  return (
    <>
      <AppBar
        title="Inscripción 2026"
        subtitle={`Paso ${step} de 5`}
        onBack={back}
        action={
          <button className="icon-btn">
            <HelpCircle />
          </button>
        }
      />
      <Stepper step={step} />
      <Page className="enrollment">
        {step === 1 && (
          <>
            <span className="eyebrow">PASO 1</span>
            <h1>Verifica tus datos</h1>
            <p>Esta información proviene de tu ficha escolar.</p>
            <div className="readonly card">
              <div>
                <span>Estudiante</span>
                <strong>Valeria Quispe Mamani</strong>
              </div>
              <div>
                <span>Código</span>
                <strong>8640</strong>
              </div>
              <div>
                <span>Curso solicitado</span>
                <strong>5º de Secundaria — B</strong>
              </div>
            </div>
          </>
        )}
        {step === 2 && (
          <>
            <span className="eyebrow">PASO 2</span>
            <h1>Documentos</h1>
            <p>Sube fotos claras y completas de cada documento.</p>
            <div className="upload-progress">
              <span>
                <strong>3 de 5</strong>
                <small>documentos listos</small>
              </span>
              <div>
                <i />
              </div>
              <b>60%</b>
            </div>
            <div className="doc-list">
              {docs.map(([name, status, state]) => (
                <div className={state === "bad" ? "rejected" : ""}>
                  <span className={`doc-status ${state}`}>
                    {state === "ok" ? <Check /> : state === "bad" ? <AlertCircle /> : <FileText />}
                  </span>
                  <span>
                    <strong>{name}</strong>
                    <small>{status}</small>
                  </span>
                  <button onClick={camera}>{state === "ok" ? <Eye /> : <Camera />}</button>
                </div>
              ))}
            </div>
            <div className="upload-actions">
              <Button kind="secondary" onClick={camera} icon={<Camera />}>
                Tomar foto
              </Button>
              <Button kind="ghost" icon={<Image />}>
                Galería
              </Button>
            </div>
          </>
        )}
        {step === 3 && (
          <>
            <span className="eyebrow">PASO 3</span>
            <h1>Domicilio</h1>
            <p>Confirma tu dirección actual.</p>
            <label className="field">
              <span>Zona o barrio</span>
              <div>
                <MapPin />
                <input defaultValue="Barrio San Jorge" />
              </div>
            </label>
            <label className="field">
              <span>Dirección</span>
              <div>
                <Home />
                <input defaultValue="Calle Junín #437" />
              </div>
            </label>
            <label className="field">
              <span>Referencia</span>
              <div>
                <Info />
                <input defaultValue="A media cuadra de la plaza" />
              </div>
            </label>
          </>
        )}
        {step === 4 && (
          <>
            <span className="eyebrow">PASO 4</span>
            <h1>Contrato educativo</h1>
            <p>Lee el documento antes de continuar.</p>
            <div className="contract">
              <div>
                <Crest />
                <span>
                  <strong>Contrato de servicios educativos</strong>
                  <small>Gestión académica 2026</small>
                </span>
              </div>
              <p>
                El presente contrato establece las condiciones de prestación del servicio educativo
                entre la Unidad Educativa San Bernardo y la familia del estudiante...
              </p>
              <p>
                La familia declara conocer y aceptar el reglamento interno, los horarios y
                compromisos académicos correspondientes.
              </p>
              <button>
                Ver contrato completo <ChevronRight />
              </button>
            </div>
            <label className="check-row">
              <input type="checkbox" defaultChecked />
              <span>He leído y acepto el contrato educativo.</span>
            </label>
          </>
        )}
        {step === 5 && (
          <div className="success-state">
            <span>
              <CheckCircle2 />
            </span>
            <small>SOLICITUD ENVIADA</small>
            <h1>¡Preinscripción lista!</h1>
            <p>Tu pago está en verificación por Secretaría. Te avisaremos cuando sea confirmado.</p>
            <div>
              <span>Nº DE TRÁMITE</span>
              <strong>CSB-2026-008640</strong>
            </div>
            <Button kind="secondary" icon={<Download />}>
              Descargar contrato PDF
            </Button>
          </div>
        )}
        {step < 5 && (
          <div className="sticky-actions">
            {step > 1 && (
              <Button kind="secondary" onClick={() => setStep(step - 1)}>
                Anterior
              </Button>
            )}
            <Button onClick={() => setStep(step + 1)}>
              {step === 4 ? "Aceptar y enviar" : "Continuar"}
            </Button>
          </div>
        )}
      </Page>
    </>
  );
}

function CameraScreen({ back }: { back: () => void }) {
  const [state, setState] = useState<"live" | "preview" | "upload" | "denied">("live");
  useEffect(() => {
    if (state === "upload") {
      const t = setTimeout(back, 1500);
      return () => clearTimeout(t);
    }
  }, [state, back]);
  if (state === "denied")
    return (
      <div className="camera-denied">
        <span>
          <Camera />
        </span>
        <h1>Permiso de cámara</h1>
        <p>Necesitamos acceso a tu cámara para fotografiar el documento.</p>
        <Button icon={<Settings />}>Abrir ajustes</Button>
        <Button kind="ghost" onClick={back}>
          Volver
        </Button>
      </div>
    );
  return (
    <div className="camera-screen">
      <div className="camera-top">
        <button onClick={back}>
          <X />
        </button>
        <strong>CI — reverso</strong>
        <button onClick={() => setState("denied")}>
          <MoreHorizontal />
        </button>
      </div>
      <div className="camera-bg" />
      <div className="camera-guide">
        <span />
        <span />
        <span />
        <span />
        {state === "preview" && (
          <div className="fake-document">
            <Crest />
            <strong>ESTADO PLURINACIONAL DE BOLIVIA</strong>
            <i />
            <i />
            <i />
          </div>
        )}
      </div>
      <div className="camera-hint">
        {state === "upload" ? (
          <>
            <span className="spinner" />
            <strong>Subiendo documento…</strong>
            <small>78% · No cierres la app</small>
          </>
        ) : (
          <>
            <strong>
              {state === "preview"
                ? "¿La foto se ve clara?"
                : "Ubica el documento dentro del marco"}
            </strong>
            <small>
              {state === "preview"
                ? "Verifica que todos los datos sean legibles."
                : "Evita reflejos y mantén el teléfono firme."}
            </small>
          </>
        )}
      </div>
      {state === "live" ? (
        <div className="camera-controls">
          <button>
            <Zap />
          </button>
          <button className="shutter" onClick={() => setState("preview")} />
          <button>
            <RefreshCw />
          </button>
        </div>
      ) : state === "preview" ? (
        <div className="preview-actions">
          <Button kind="secondary" onClick={() => setState("live")}>
            Repetir
          </Button>
          <Button onClick={() => setState("upload")}>Usar foto</Button>
        </div>
      ) : (
        <div className="upload-bar">
          <i />
        </div>
      )}
    </div>
  );
}

function Payments({ back, camera }: { back: () => void; camera: () => void }) {
  return (
    <>
      <AppBar
        title="Pagos"
        subtitle="Gestión 2025"
        onBack={back}
        action={
          <button className="icon-btn">
            <HelpCircle />
          </button>
        }
      />
      <Page>
        <div className="account-card">
          <div>
            <small>ESTADO DE CUENTA</small>
            <strong>Bs 785,50</strong>
            <span>Saldo pendiente</span>
          </div>
          <span>
            <small>Pagado</small>
            <strong>Bs 1.571,00</strong>
          </span>
        </div>
        <div className="payment-progress">
          <span>
            <strong>2 de 10 mensualidades</strong>
            <small>20% de la gestión</small>
          </span>
          <div>
            <i />
          </div>
        </div>
        <SectionTitle>Mensualidades</SectionTitle>
        {[
          ["Marzo", "Bs 785,50", "Pagado", "05/03/2025", "present"],
          ["Abril", "Bs 785,50", "Pendiente", "Vence 15/04/2025", "late"],
          ["Mayo", "Bs 785,50", "Próximamente", "Vence 15/05/2025", "neutral"],
        ].map((x, i) => (
          <div className="payment-card">
            <span className="month">{x[0].slice(0, 3).toUpperCase()}</span>
            <span>
              <strong>
                {x[0]} · {x[1]}
              </strong>
              <small>{x[3]}</small>
            </span>
            <Chip tone={x[4]}>{x[2]}</Chip>
            {i === 1 && (
              <button onClick={camera}>
                <Upload /> Subir comprobante
              </button>
            )}
          </div>
        ))}
        <div className="info-box">
          <Info />
          <p>
            <strong>Validación de pagos</strong>El pago se confirma en Secretaría en un plazo de
            hasta 48 horas.
          </p>
        </div>
      </Page>
    </>
  );
}

function Toggle({ initial = true }: { initial?: boolean }) {
  const [on, setOn] = useState(initial);
  return (
    <button
      aria-label="Cambiar preferencia"
      onClick={() => setOn(!on)}
      className={`toggle ${on ? "on" : ""}`}
    >
      <i />
    </button>
  );
}

function Profile({ go }: { go: (s: Screen) => void }) {
  return (
    <>
      <AppBar
        title="Mi perfil"
        action={
          <button className="icon-btn">
            <Settings />
          </button>
        }
      />
      <Page>
        <div className="profile-head">
          <div className="avatar avatar-xl">
            VQ
            <button>
              <Camera />
            </button>
          </div>
          <h1>Valeria Quispe Mamani</h1>
          <p>4º de Secundaria — Paralelo B</p>
          <Chip>Código 8640 · Turno mañana</Chip>
        </div>
        <p className="list-caption">MI CUENTA</p>
        <div className="settings-card">
          <button onClick={() => go("profileSetup")}>
            <span>
              <User />
            </span>
            <div>
              <strong>Datos personales</strong>
              <small>Teléfono, dirección y foto</small>
            </div>
            <ChevronRight />
          </button>
          <button>
            <span>
              <ShieldCheck />
            </span>
            <div>
              <strong>Seguridad</strong>
              <small>Cuenta institucional</small>
            </div>
            <ChevronRight />
          </button>
        </div>
        <p className="list-caption">NOTIFICACIONES</p>
        <div className="settings-card">
          <div>
            <span>
              <MessageSquareText />
            </span>
            <div>
              <strong>Comunicados</strong>
              <small>Novedades del colegio</small>
            </div>
            <Toggle />
          </div>
          <div>
            <span>
              <BookOpen />
            </span>
            <div>
              <strong>Notas</strong>
              <small>Nuevas calificaciones</small>
            </div>
            <Toggle />
          </div>
          <div>
            <span>
              <CalendarDays />
            </span>
            <div>
              <strong>Asistencia</strong>
              <small>Faltas y retrasos</small>
            </div>
            <Toggle initial={false} />
          </div>
        </div>
        <p className="list-caption">MÁS</p>
        <div className="settings-card">
          <button>
            <span>
              <HelpCircle />
            </span>
            <div>
              <strong>Ayuda y soporte</strong>
            </div>
            <ChevronRight />
          </button>
          <button className="logout" onClick={() => go("login")}>
            <span>
              <LogOut />
            </span>
            <div>
              <strong>Cerrar sesión</strong>
            </div>
          </button>
        </div>
        <p className="version">CSB 360 · Versión 1.0.0</p>
      </Page>
    </>
  );
}

export default function App() {
  const [screen, setScreen] = useState<Screen>(() => {
    const requested = new URLSearchParams(window.location.search).get("screen") as Screen | null;
    return requested && screens.includes(requested) ? requested : "splash";
  });
  const [history, setHistory] = useState<Screen[]>([]);
  useEffect(() => {
    if (screen === "splash") {
      const t = setTimeout(() => setScreen("login"), 1350);
      return () => clearTimeout(t);
    }
  }, [screen]);
  const go = (next: Screen) => {
    setHistory((h) => [...h, screen]);
    setScreen(next);
    window.scrollTo(0, 0);
  };
  const back = () => {
    setHistory((h) => {
      const next = h[h.length - 1] || "home";
      setScreen(next);
      return h.slice(0, -1);
    });
    window.scrollTo(0, 0);
  };
  const activeTab = useMemo(() => (tabs.some((t) => t.id === screen) ? screen : "home"), [screen]);
  const showTabs = tabs.some((t) => t.id === screen);

  return (
    <div className="app-shell">
      <div className="phone">
        <div className="statusbar">
          <span>9:41</span>
          <span>
            <Zap size={13} fill="currentColor" />
            <WifiOff size={13} />
            <b>78</b>
          </span>
        </div>
        <div className={`screen ${showTabs ? "with-tabs" : ""}`}>
          {screen === "splash" && <Splash />}
          {screen === "login" && (
            <Login onLogin={() => go("home")} onDemo={() => go("profileSetup")} />
          )}
          {screen === "profileSetup" && <ProfileSetup done={() => go("home")} />}
          {screen === "home" && <HomeScreen go={go} />}
          {screen === "subjects" && <Subjects go={go} back={back} />}
          {screen === "subject" && <SubjectDetail back={back} />}
          {screen === "grades" && <Grades />}
          {screen === "attendance" && <Attendance />}
          {screen === "schedule" && <Schedule back={back} />}
          {screen === "announcements" && <Announcements go={go} />}
          {screen === "announcement" && <Announcement back={back} />}
          {screen === "enrollment" && <Enrollment back={back} camera={() => go("camera")} />}
          {screen === "camera" && <CameraScreen back={back} />}
          {screen === "payments" && <Payments back={back} camera={() => go("camera")} />}
          {screen === "profile" && <Profile go={go} />}
          {showTabs && <TabBar active={activeTab} go={go} />}
        </div>
      </div>
      <div className="desktop-note">
        <Crest />
        <strong>CSB 360</strong>
        <span>Portal móvil del estudiante</span>
        <p>Diseñado para acompañarte en cada paso de tu gestión escolar.</p>
      </div>
    </div>
  );
}
