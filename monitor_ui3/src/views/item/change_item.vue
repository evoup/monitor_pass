<template>
  <div class="app-container">
    <el-form ref="form" :model="form" label-width="120px">
      <el-form-item label="名称">
        <el-col :span="8">
          <el-input v-model="form.name" placeholder="请输入名称"/>
        </el-col>
      </el-form-item>
      <el-form-item label="键">
        <el-col :span="8">
          <el-input v-model="form.key" placeholder="请输入或选择键"/>
        </el-col>
      </el-form-item>
      <el-form-item label="数据类型">
        <el-col :span="8">
          <el-select v-model="dataTypeOptionModel" placeholder="请输入或选择键" value="1">
            <el-option
              v-for="(item, index) in dataTypeOptions"
              :key="index"
              :label="item.label"
              :value="item.value"
            />
          </el-select>
        </el-col>
      </el-form-item>
      <el-form-item label="数据倍率">
        <el-col :span="8">
          <el-input v-model="form.multiplier" placeholder="数据倍率"/>
        </el-col>
      </el-form-item>
      <el-form-item label="数据存储">
        <el-col :span="8">
          <el-select v-model="dataStoreOptionModel" placeholder="请选择" value="1">
            <el-option
              v-for="(item, index) in dataStoreOptions"
              :key="index"
              :label="item.label"
              :value="item.value"
            />
          </el-select>
        </el-col>
      </el-form-item>
      <el-form-item label="数据展示">
        <el-col :span="8">
          <el-select v-model="dataShowOptionModel" placeholder="请输入或选择键" value="1">
            <el-option
              v-for="(item, index) in dataShowOptions"
              :key="index"
              :label="item.label"
              :value="item.value"
            />
          </el-select>
        </el-col>
      </el-form-item>
      <el-form-item label="数据单位">
        <el-col :span="8">
          <el-input v-model="form.unit" placeholder="请输入数据单位"/>
        </el-col>
      </el-form-item>
      <el-form-item label="描述">
        <el-col :span="14">
          <el-input v-model="form.desc" placeholder="请输入描述"/>
        </el-col>
      </el-form-item>
      <el-form-item>
        <el-button type="primary" @click="addServer(form.name,form.client, form.jmx, form.snmp, form.idc)">创建
        </el-button>
        <el-button @click="jumpServerList">取消</el-button>
      </el-form-item>
    </el-form>
  </div>
</template>

<script>
import { read_item } from '../../api/item'

export default {
  data() {
    return {
      dataTypeOptions: [
        {
          value: '1',
          label: '无符号数字'
        },
        {
          value: '2',
          label: '浮点数字'
        },
        {
          value: '3',
          label: '文字'
        }
      ],
      dataTypeOptionModel: '1',
      dataStoreOptions: [
        {
          value: '1',
          label: '保持不变'
        },
        {
          value: '2',
          label: '增量'
        },
        {
          value: '3',
          label: '每秒增量'
        }
      ],
      dataStoreOptionModel: '1',
      dataShowOptions: [
        {
          value: '1',
          label: '保持不变'
        },
        {
          value: '2',
          label: '主机状态'
        },
        {
          value: '3',
          label: '服务状态'
        }
      ],
      dataShowOptionModel: '1',
      form: {
        client: '',
        name: '',
        jmx: '',
        snmp: '',
        idc: '',
        region: '',
        date1: '',
        date2: '',
        delivery: false,
        type: [],
        resource: '',
        desc: ''
      }
    }
  },
  mounted() {
    this.readItem({ id: this.$route.query.id })
  },
  methods: {
    readItem(id) {
      read_item(id).then(response => {
        this.form.name = response.data.item.name
        this.form.key = response.data.item.key
        this.form.multiplier = response.data.item.multiplier
        this.form.unit = response.data.item.unit
        this.form.desc = response.data.item.desc
      })
    },
    jumpServerList() {
      this.$router.push({ path: '/server_list' })
    }
  }
}
</script>

<style scoped>

</style>
