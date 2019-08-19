<template>
  <div class="app-container">
    <el-tabs type="border-card">
      <!-- tab-pane1 -->
      <el-tab-pane label="明细状态">
        <div id="status">
          <ul class="list_1">

            <li>
              <strong>主机名：</strong>
              <span>
                bam4atd50monitor01-a0a3010
              </span>
            </li>

            <li>
              <strong>IP地址：</strong>
              <span>
                10.10.48.16
              </span>
            </li>

            <li>
              <strong>运行时间：</strong>
              <span>
                22.53d 22.55h m s
              </span>
            </li>

            <li>
              <strong>上次上传：</strong>
              <span>
                2019-08-19 06:56:22
              </span>
            </li>

            <li>
              <strong>上次更新：</strong>
              <span>
                2019-08-19 06:56:08
              </span>
            </li>

            <li>
              <strong>客户端版本号：</strong>
              <span>
                版本过低,需更新
              </span>
            </li>

          </ul>
          <div id="detail">
            <ul class="list_1">

              <li>
                <strong>Load Average：</strong>
                <span>
                  22.52
                </span>
              </li>

              <li>
                <strong>TCP连接数：</strong>
                <span>
                  15
                </span>
              </li>

              <li>
                <strong>cpu：</strong>
                <span>
                  usage:93.89% use:16.9 nice:0.0 system:76.9 interrupt:0 idle:6.1
                </span>
              </li>

              <li>
                <strong>内存：</strong>
                <span>
                  active:2.86Gb inactive:0Bytes wired:0Bytes cache:0Bytes buf:0Bytes free:900.12Mb
                </span>
              </li>

              <li>
                <strong>SWAP：</strong>
                <span>
                  total:3.87Gb used:8.52Mb free:3.87Gb inuse: 1.32Gb
                </span>
              </li>

              <li>
                <strong>磁盘：</strong>
                <span>
                  disk:/ 29% disk:/boot 7% disk:/dev/shm 0%
                </span>
              </li>

              <li>
                <strong>文件系统Inode：</strong>
                <span>
                  inode:/ 52% inode:/boot 1% inode:/dev/shm 1%
                </span>
              </li>

              <li>
                <strong>进程：</strong>
                <span>
                  sum:0 starting:0 running:4 sleeping:198 stopped:0 zombie:41 waiting:0 lock:0
                </span>
              </li>

              <li>
                <strong>网络接口：</strong>
                <span>
                  -
                </span>
              </li>

            </ul>
          </div>
        </div>
      </el-tab-pane>
      <el-tab-pane label="实时监控图表">
        <el-button type="primary" style="min-width:10%;" @click.native.prevent="genGrafana">
          添加
        </el-button>
      </el-tab-pane>
    </el-tabs>
  </div>
</template>

<script>
export default {
  name: 'ServerDetail',
  methods: {

  }
}
</script>

<style scoped>
.app-container /deep/ .list_1 li {
  list-style-type: none;
  font-size: 14px;
  line-height: 30px;
  color:#606266;
}
</style>
