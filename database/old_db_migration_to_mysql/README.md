# FUMA sqlite db migration to mysql instructions:

1. Run:
```
conda env create -f conda_environment.yml
```

2. Execute `fuma_new_tmp.sql` queries in mysql. For example run:
```
mysql -h 127.0.0.1  -u root -p < fuma_new_tmp.sql
```

3. Comment in/out as follows:
```
# return date.fromisoformat(value.decode())
return datetime.strptime(value.decode(), "%Y-%m-%d %H:%M:%S")
```
in `miniconda3\envs\sqlite3-to-mysql\lib\python3.10\site-packages\sqlite3_to_mysql\sqlite_utils.py`

4. Run:
``` 
sqlite3mysql -f database.sqlite -d fuma_new_tmp -u root --mysql-password root -W -t SubmitJobs gene2func password_resets JobMonitor celltype users failed_jobs
```

5. Comment in/out as follows:
```
return date.fromisoformat(value.decode())
# return datetime.strptime(value.decode(), "%Y-%m-%d %H:%M:%S")
```
in `miniconda3\envs\sqlite3-to-mysql\lib\python3.10\site-packages\sqlite3_to_mysql\sqlite_utils.py`

6. Run:
``` 
sqlite3mysql -f database.sqlite -d fuma_new_tmp -u root --mysql-password root -W -t PublicResults
```

7. Execute `queries.sql` queries. 