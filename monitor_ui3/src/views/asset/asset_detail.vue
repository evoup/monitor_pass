<template>
  <div class="app-container">
    <el-tabs type="border-card">
      <el-tab-pane label="基本信息">
        <el-row>
          <el-col :span="8">
            主机名： {{ x.host_name }}
          </el-col>
        </el-row>
        <el-row>
          <el-col :span="8">
            序列号： {{ x.serial_number }}
          </el-col>
        </el-row>
        <el-row>
          <el-col :span="8">
            IP： {{ x.ip }}
          </el-col>
        </el-row>
        <el-row>
          <el-col :span="8">
            状态： {{ x.status }}
          </el-col>
        </el-row>
        <el-row>
          <el-col :span="8">
            更新时间： {{ x.update_time }}
          </el-col>
        </el-row>
        <el-row>
          <el-col :span="8">
            机房： {{ x.idc }}
          </el-col>
        </el-row>
        <el-row>
          <el-col :span="8">
            机柜： {{ x.carinet }}
          </el-col>
        </el-row>
        <el-row>
          <el-col :span="8">
            柜上位置： {{ x.position }}
          </el-col>
        </el-row>
        <el-row>
          <el-col :span="8">
            业务线： {{ x.business_line }}
          </el-col>
        </el-row>
      </el-tab-pane>
      <el-tab-pane label="硬件信息">
        <table class="guige-table">
          <tbody>
            <tr>
              <th colspan="5" class="title f14 th"><span class="padding-l-8">基础信息</span></th>
            </tr>
            <tr>
              <td>名称</td>
              <td colspan="4">值</td>
            </tr>
            <tr>
              <td>操作系统</td>
              <td colspan="4">Linux的</td>
            </tr>
            <tr>
              <td>系统版本</td>
              <td colspan="4">CentOS 6.6版（最终版）</td>
            </tr>
            <tr>
              <td>主板序列号</td>
              <td colspan="4">Parallels-1A 1B CB 3B 64 66 4B 13 86 B0 86 FF 7E 2B 20 30</td>
            </tr>
            <tr>
              <td>主板型号</td>
              <td colspan="4">Parallels虚拟平台</td>
            </tr>
            <tr>
              <td>主板厂商</td>
              <td colspan="4">Parallels Software International Inc</td>
            </tr>
            <tr>
              <td>CPU逻辑核数</td>
              <td colspan="4">24</td>
            </tr>
            <tr>
              <td>CPU物理核数</td>
              <td colspan="4">2</td>
            </tr>
            <tr>
              <td>CPU型号</td>
              <td colspan="4">英特尔（R）Xeon（R）CPU E5-2620 v2 @ 2.10GHz</td>
            </tr>

            <!-- 网卡信息开始 -->
            <tr>
              <th colspan="5" class="title f14" style="border-left:2px solid #0488cd;"><span class="padding-l-8">网卡信息</span></th>
            </tr>
            <tr>
              <td>名称</td>
              <td>MAC地址</td>
              <td>IP地址</td>
              <td>掩码</td>
              <td>UP</td>
            </tr>
            <tr>
              <td>为eth0</td>
              <td>00：1c：42：a5：57：7a</td>
              <td>10.211.55.4</td>
              <td>255.255.255.0</td>
              <td>是</td>
            </tr>
            <!-- 网卡信息结束 -->
            <!-- 硬盘信息开始 -->
            <tr>
              <th colspan="5" class="title f14" style="border-left:2px solid #0488cd;">
                <span class="padding-l-8">硬盘信息</span>
              </th>
            </tr>
            <tr>
              <td>插槽</td>
              <td>容量</td>
              <td>接口类型</td>
              <td colspan="2">型号</td>
            </tr>
            <tr>
              <td>1</td>
              <td>279.396</td>
              <td>SAS</td>
              <td colspan="2">希捷ST300MM0006 LS08S0K2B5AH</td>
            </tr>
            <tr>
              <td>5</td>
              <td>476.939</td>
              <td>SATA</td>
              <td colspan="2">S1AXNSAFB00549A三星SSD 840 PRO系列DXM06B0Q</td>
            </tr>
            <tr>
              <td>2</td>
              <td>476.939</td>
              <td>SATA</td>
              <td colspan="2">S1SZNSAFA01085L三星SSD 850 PRO 512GB EXM01B6Q</td>
            </tr>
            <tr>
              <td>3</td>
              <td>476.939</td>
              <td>SATA</td>
              <td colspan="2">S1AXNSAF912433K三星SSD 840 PRO系列DXM06B0Q</td>
            </tr>
            <tr>
              <td>0</td>
              <td>279.396</td>
              <td>SAS</td>
              <td colspan="2">希捷ST300MM0006 LS08S0K2B5NV</td>
            </tr>
            <tr>
              <td>4</td>
              <td>476.939</td>
              <td>SATA</td>
              <td colspan="2">S1AXNSAF303909M三星SSD 840 PRO系列DXM05B0Q</td>
            </tr>
            <!-- 硬盘信息结束 -->
            <!-- 内存信息开始 -->
            <tr>
              <th colspan="5" class="title f14" style="border-left:2px solid #0488cd;">
                <span class="padding-l-8">内存信息</span>
              </th>
            </tr>
            <tr>
              <td>插槽</td>
              <td>容量</td>
              <td>频率</td>
              <td colspan="2">型号</td>
            </tr>
            <tr>
              <td>DIMM＃2</td>
              <td>0.0</td>
              <td>667 MHz</td>
              <td colspan="2">DRAM</td>
            </tr>
            <tr>
              <td>DIMM＃5</td>
              <td>0.0</td>
              <td>667 MHz</td>
              <td colspan="2">DRAM</td>
            </tr>
            <tr>
              <td>DIMM＃7</td>
              <td>0.0</td>
              <td>667 MHz</td>
              <td colspan="2">DRAM</td>
            </tr>
            <tr>
              <td>DIMM＃3</td>
              <td>0.0</td>
              <td>667 MHz</td>
              <td colspan="2">DRAM</td>
            </tr>
            <tr>
              <td>DIMM＃6</td>
              <td>0.0</td>
              <td>667 MHz</td>
              <td colspan="2">DRAM</td>
            </tr>
            <tr>
              <td>DIMM＃4</td>
              <td>0.0</td>
              <td>667 MHz</td>
              <td colspan="2">DRAM</td>
            </tr>
            <tr>
              <td>DIMM＃0</td>
              <td>1024.0</td>
              <td>667 MHz</td>
              <td colspan="2">DRAM</td>
            </tr>
            <tr>
              <td>DIMM＃1</td>
              <td>0.0</td>
              <td>667 MHz</td>
              <td colspan="2">DRAM</td>
            </tr>
          <!-- 内存信息结束 -->

          </tbody>
        </table>
      </el-tab-pane>
      <el-tab-pane label="变更记录"/>
    </el-tabs>
  </div>
</template>

<script>
export default {
  name: 'AssetDetail',
  data() {
    return {
      x: {
        host_name: 'server1',
        serial_number: 'Parallels-1A 1B CB 3B 64 66 4B 13 86 B0 86 FF 7E 2B 20 30',
        'ip': '172.12.11.3',
        'status': '上架',
        'update_time': '2019-07-17',
        'idc': '张江机房',
        'carinet': '12s',
        'floor': '12f',
        'position': '5',
        'business_line': '广告投放'
      }
    }
  }
}
</script>

<style scoped>
  .app-container /deep/ table {
    font-size: 14px;
    text-align: left;
    width:100%;
    line-height: 30px;
    height: 30px;
    border-collapse: collapse;
    border: 1px solid #dcdcdc;
  }
  .app-container /deep/ table th {
    padding:0 10px;
  }
  .app-container /deep/ table td {
    padding:0 10px;
    border: 1px solid #dcdcdc;
  }
</style>
