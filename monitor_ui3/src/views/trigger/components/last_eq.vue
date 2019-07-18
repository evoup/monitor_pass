<template>
  <div class="person-form">

    <el-form ref="personForm" :model="personForm" :rules="personFormRules">
      <!-- 姓名 -->
      <el-form-item label="姓名" prop="name">
        <el-input v-model="personForm.name"/>
      </el-form-item>
      <!-- 年龄 -->
      <el-form-item label="年龄" prop="age">
        <el-input v-model="personForm.age"/>
      </el-form-item>
      <!-- 性别 -->
      <el-form-item label="性别" prop="sex">
        <el-radio-group v-model="personForm.sex">
          <el-radio label="0">男</el-radio>
          <el-radio label="1">女</el-radio>
        </el-radio-group>
      </el-form-item>
    </el-form>
  </div>
</template>

<script>
import { validateName, validateAge, validateSex } from '../../../lib/validator.js'


class PersonForm {
  constructor() {
    this.name = ''
    this.age = null
    this.sex = null
  }

  static getRule() {
    return {
      name: [{ validator: validateName, trigger: 'blur' }],
      age: [{ validator: validateAge, trigger: 'blur' }],
      sex: [{ validator: validateSex, trigger: 'blur' }]
    }
  }
}

export default {
  data() {
    return {
      personForm: new PersonForm(),
      personFormRules: PersonForm.getRule()
    }
  }
}
</script>

<style>
  .person-form {
    width: 400px;
    height: 350px;
    padding: 20px;
    border: 1px solid #ccc;
  }
</style>
