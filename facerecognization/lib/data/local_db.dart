import 'package:sqflite/sqflite.dart';
import 'package:path/path.dart';

class LocalDatabase {
  static final LocalDatabase instance = LocalDatabase._init();
  static Database? _database;

  LocalDatabase._init();

  Future<Database> get database async {
    if (_database != null) return _database!;
    _database = await _initDB('attendance_cache.db');
    return _database!;
  }

  Future<Database> _initDB(String filePath) async {
    final dbPath = await getDatabasesPath();
    final path = join(dbPath, filePath);

    return await openDatabase(
      path,
      version: 1,
      onCreate: _createDB,
    );
  }

  Future _createDB(Database db, int version) async {
    await db.execute('''
      CREATE TABLE employees (
        id INTEGER PRIMARY KEY,
        employeeId TEXT NOT NULL,
        firstName TEXT NOT NULL,
        lastName TEXT NOT NULL,
        email TEXT NOT NULL,
        status TEXT NOT NULL
      )
    ''');

    await db.execute('''
      CREATE TABLE face_embeddings (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        employeeId INTEGER NOT NULL,
        embeddingVector TEXT NOT NULL,
        captureAngle TEXT NOT NULL
      )
    ''');

    await db.execute('''
      CREATE TABLE attendance_logs (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        employeeId INTEGER NOT NULL,
        clockTime TEXT NOT NULL,
        clockType TEXT NOT NULL,
        status TEXT NOT NULL,
        deviceId TEXT NOT NULL,
        isSynced INTEGER NOT NULL DEFAULT 0
      )
    ''');
  }

  Future<void> cacheEmployee(Map<String, dynamic> emp) async {
    final db = await database;
    await db.insert('employees', emp, conflictAlgorithm: ConflictAlgorithm.replace);
  }

  Future<void> cacheFaceEmbedding(Map<String, dynamic> emb) async {
    final db = await database;
    await db.insert('face_embeddings', emb, conflictAlgorithm: ConflictAlgorithm.replace);
  }

  Future<void> insertAttendanceLog(Map<String, dynamic> log) async {
    final db = await database;
    await db.insert('attendance_logs', log);
  }

  Future<List<Map<String, dynamic>>> getUnsyncedLogs() async {
    final db = await database;
    return await db.query('attendance_logs', where: 'isSynced = ?', whereArgs: [0]);
  }

  Future<void> markSynced(int id) async {
    final db = await database;
    await db.update('attendance_logs', {'isSynced': 1}, where: 'id = ?', whereArgs: [id]);
  }
}
