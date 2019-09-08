<template>
  <div class="app-container">
    <el-form ref="form" :model="form" label-width="140px">
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
        <el-col :span="2">
          <el-input v-model="form.multiplier" placeholder="数据倍率"/>
        </el-col>
      </el-form-item>
      <el-form-item label="数据收集间隔秒数">
        <el-col :span="4">
          <el-input v-model="form.interval" placeholder="数据收集间隔秒数"/>
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
      <el-form-item label="数值显示">
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
        <el-col :span="4">
          <el-input v-model="form.unit" placeholder="请输入数据单位"/>
        </el-col>
      </el-form-item>
      <el-form-item label="描述">
        <el-col :span="14">
          <el-input v-model="form.desc" placeholder="请输入描述"/>
        </el-col>
      </el-form-item>
      <el-form-item>
        <el-button type="primary" @click="addItem(form.name, form.key, form.multiplier, form.interval, dataShowOptionModel, form.unit, form.desc)">创建</el-button>
        <el-button @click="$router.back(-1)">取消</el-button>
      </el-form-item>
    </el-form>
  </div>
</template>

<script>
import { add_item } from '../../api/item'
export default {
  data() {
    return {
      dataTypeOptions: [
        {
          value: '1',
          label: '整数'
        },
        {
          value: '2',
          label: '浮点数'
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
          label: '保持原样'
        },
        {
          value: '2',
          label: '增量'
        }
      ],
      dataStoreOptionModel: '1',
      dataShowOptions: [
        {
          value: '1',
          label: '保持原样'
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
        name: '',
        key: '',
        multiplier: '1',
        interval: '60',
        dataShowOptionModel: '',
        unit: '',
        desc: ''
      }
    }
  },
  methods: {
    addItem(a, b, c, d, e, f, g) {
      add_item(a, b, c, d, e, f, g, this.$route.query.template_id)
    }
  }
}
</script>

<style scoped>

</style>
