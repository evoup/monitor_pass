### monitor data collector

### 运行
```bash
java -Xms256m -Xmx512m -Dexternal_conf=/path/to/conf/ -jar target/xxx.jar
```
监控界面上的数据收集器，必须设置和配置文件中相同的datacollector.servername的值，否则不会入库。
