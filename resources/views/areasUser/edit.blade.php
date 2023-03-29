
@extends('layouts.app')
@section('header_title', 'Areas del usuario')
@section('header_subtitle', ': Defina a que áreas pertenece o puede acceder el usuario.')
@section('camino')
  <li class="breadcrumb-item active"> <i class="fa fa-users"></i> Áreas del usuario</li>
@endsection

@section('content')
  <div class="row" id="app">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
      <div class="card card-primary">
        <div class="card-header with-border">
          <h3 class="card-title">Seleccione las áreas para el usuario: {{$user->name}}</h3>
          <div class="card-tools pull-right">

          </div>
        </div>
        <div class="card-body" >
          <div class="row">
            {{-- Arbol del sistema --}}
            <div class="col-6" style="max-height: 500px; overflow-y: auto;">
              <h5 class="pl-2">
                Areas del usuario
                <span @click="saveAreasUser" class="btn btn-success btn-xs ml-5">Guardar áreas del usuario</span>
              </h5>
              <hr>
              <div v-if="customTree" v-for="root in customTree">
                <node-component
                  class="pl-5"
                  :node="root">
                </node-component>
              </div>
            </div>

            {{-- Roles por área --}}
            <div class="col-6" style="max-height: 500px; overflow-y: auto;">
              <h5 class="pl-2">
                Roles por área
                <span @click="saveUserAreasRoles" class="btn btn-success btn-xs ml-5">Guardar roles por áreas</span>
              </h5>

              <hr>

              <div v-if="selectedNodes" v-for="(area, index) in selectedNodes">
                <div class="form-group">
                  <span>@{{area.parent_name}} > <b>@{{area.nombre}}</b></span>
                  <div>
                    <v-select
                      class="select-roles"
                      :id="area.id+'_'+area.parent_id+'_'+index"
                      :options="roles"
                      :value="area.roles"
                      >
                    </v-select>
                  </div>
                </div>
              </div>
            </div>

          </div>
        </div>
      </div>
    </div>
  </div>
@endsection


@push('page_scripts')
  <script src="{{asset('js/vue.js')}}"></script>

  <script>
    const appUrl = "{{ url('/'); }}";
    let storedConfig = JSON.parse(`{!! $storedConfig !!}`);
    let areasUser = JSON.parse(`{!! $areasUser !!}`);

    //componentes
    const NodeComponent = {
      props: ['node'],
      data: function () {
        return {
          checked: this.node.selected,
        }
      },
      methods: {
        selectNode(){
          this.node.selected = !this.checked;
        }
      },
      template: `
      <div style="border-left: solid 1px !important;  border-left-color: #3f6791 !important;">
        <span :id="node.id" >
          <input type="checkbox" v-model="checked" @click="selectNode()">
          @{{node.nombre}}
        </span>

        <node-component
          class="pl-5"
          v-if="node.children && node.children.length"
          v-for="child in node.children"
          :node="child">
        </node-component>
      </div>
      `
    };

    const VueSelect = {
      props: ['options', 'value', 'name', 'id', 'clases', 'placeholder'],
      // template: '#select2-template',
      template: `
        <select multiple v-bind:name="name" v-bind:id="id" v-bind:class="clases">
          <slot></slot>
        </select>
      `,
      mounted: function () {
        var vm = this

        $(this.$el)
          // init select2
          .select2({ data: this.ComputedOptions, 'width':'100%', placeholder: {id:'-1' ,text:this.placeholder} })
          .val(this.value)
          .trigger('change')
          // emit event on change.
          .on('change', function () {
            vm.$emit('input', this.value)
            vm.$emit('change')
          })
      },
      methods: {
        // change: function () {
        //   this.$emit('change')
        // }
      },
      computed: {
        ComputedOptions: function(){
          if (Array.isArray(this.options))
          {
            var options = this.options.map(function(obj) {
              return {id: obj.id, text: obj.name };
            });
          }
          else
          {
            let obj = this.options;
            var options = Object.keys(obj).map(function(key) {
              return {id: parseInt(key), text: obj[key] };
            });
          }

          return options;
        }
      },
      watch: {
        value: function (value) {
          // update value
          $(this.$el)
            .val(value)
            .trigger('change')
        },
        options: function (options) {
          // update options
          $(this.$el).empty().select2({ data: this.ComputedOptions, 'width':'100%', placeholder: {id:'-1' ,text:this.placeholder} })
        }
      },
      destroyed: function () {
        $(this.$el).off().select2('destroy')
      }
    };

    // isntancia de VUE
    const { createApp } = Vue;
    const app = createApp ({
      data() {
        return {
          id_user: `{!! $user->id !!}`,
          customTree: null,
          customNodes: [],
          relacionesNiveles: [],
          levelsTypes: [],
          roles: [],
          user_areas_roles: [],
        }
      },
      computed: {
        selectedNodes(){
          let nodes = [];
          if(this.customTree){
            for (const branch of this.customTree) {
              let branchNodes = this.getCustomTreeNodes(branch);
              for (const node of branchNodes) {
                let padre = this.relacionesNiveles.find(e=>e.id_nivel_hijo == node.parent_id);
                node.parent_name = padre ? padre.nivel_hijo.nombre : null;
                if(! nodes.find(e=>e.id == node.id && e.parent_id == node.parent_id)){
                  node.roles =[];
                  let roles = this.user_areas_roles.filter(e=>e.id_area == node.id && e.id_parent == node.parent_id);
                  if (roles){
                    roles = roles.map(e=>{ return e.id_rol});
                  }
                  node.roles = roles;
                  nodes.push(node);
                }
              }
            }
          }
          return nodes;
        }
      },
      methods: {
        async getRoles(){
          const response = await $.ajax({
            type: "GET",
            url: appUrl + '/areas-user-get-roles',
            dataType: "json"
          });
          this.roles = response;
        },
        async getRelacionesNiveles(){
          const response = await $.ajax({
            type: "GET",
            url: appUrl + '/api-organigrama-get-relaciones-niveles',
            dataType: "json"
          });
          this.relacionesNiveles = response;
        },
        async getUserAreasRoles(){
          const response = await $.ajax({
            type: "GET",
            url: appUrl + '/areas-user-get-user-areas-roles',
            data: {id_user: this.id_user},
            dataType: "json"
          });
          this.user_areas_roles = response;
        },
        getCustomTreeNodes(elem=null, result=[], level=0){
          if(elem instanceof Array) {
            for(var i = 0; i < elem.length; i++) {
              this.getCustomTreeNodes(elem[i], result, level+1);
            }
          }
          else
          {
            if(elem.hasOwnProperty('selected') && elem.selected ){
              result.push({
                id: elem.id,
                nombre: elem.nombre,
                descripcion: elem.descripcion,
                selected: elem.selected,
                tipo_id: elem.tipo_id,
                parent_id: elem.parent_id? elem.parent_id: 0,
                level: level+1,
              });
            }

            if(elem.hasOwnProperty('children') && elem.children instanceof Array && elem.children.length){
              this.getCustomTreeNodes(elem.children, result, level+1);
            }
          }
          return result;
        },
        getCustomTree(parent){
          let children = this.customNodes.filter(e=> e.parent_id == parent.id);
          if(children) {
            parent.children = [];
            for (const child of children) {
              let childData = {
                id: child.id,
                nombre: child.nombre,
                descripcion: child.descripcion,
                tipo_id: child.tipo_id,
                tipo: child.tipo,
                parent_id: child.parent_id
              }
              parent.children.push(childData);
              this.getCustomTree(childData);
            }
          }
          return parent;
        },
        async saveAreasUser(){
          if(!this.customTree) return false;
          $.ajaxSetup({
              headers: {
                  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
              }
          });
          try{
            const response = await $.ajax({
              type: "PUT",
              url: appUrl + '/areas-user/'+this.id_user,
              data: {tree: JSON.stringify(this.customTree)},
              dataType: "json"
            });
            Swal.fire('Bien!', 'configuración guardada.' , 'success');
            window.location.href= appUrl+"/users/"+this.id_user+"/edit";
          }
          catch(ex){
            console.error('Error al guardar: ',ex.responseJSON.error);
            Swal.fire('Error al guardar', ex.responseJSON.error , 'error');
          }
        },
        async saveUserAreasRoles(){
          if(!this.customTree) return false;

          let rolesXArea = [];
          $('.select-roles').each(function(index, e){
            let id = $(e).attr('id');
            id = id.split('_');
            let roles = $(e).val();
            rolesXArea.push({id:id[0], parent_id:id[1], roles:roles});
          });

          $.ajaxSetup({
              headers: {
                  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
              }
          });
          try{
            const response = await $.ajax({
              type: "PUT",
              url: appUrl + '/areas-user-set-user-areas-roles/'+this.id_user,
              data: {rolesXArea: JSON.stringify(rolesXArea)},
              dataType: "json"
            });
            Swal.fire('Bien!', 'configuración guardada.' , 'success');
            window.location.href= appUrl+"/users/"+this.id_user+"/edit";
          }
          catch(ex){
            console.error('Error al guardar: ',ex.responseJSON.error);
            Swal.fire('Error al guardar', ex.responseJSON.error , 'error');
          }
        },
      },
      async mounted() {
        await this.getRoles();
        await this.getUserAreasRoles();
        await this.getRelacionesNiveles();
        if(areasUser.length)
          this.customTree = areasUser;
        else
          this.customTree = storedConfig;
      }
    })
    .component("node-component", NodeComponent)
    .component('v-select', VueSelect)
    .mount('#app');

  </script>
@endpush
