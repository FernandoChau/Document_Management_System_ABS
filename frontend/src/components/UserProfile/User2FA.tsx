// import React from 'react'
import Button from '../ui/button/Button'

function User2FA() {
  return (
    <div className="p-5 border border-gray-200 rounded-2xl dark:border-gray-800 lg:p-6">
      <div className="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
        <div>
          <h4 className="text-lg font-semibold text-gray-800 dark:text-white/90 lg:mb-6">
            Dois Factores de Autenticação
          </h4>

          <div className="flex gap-2">
            <div>
              <p className="mb-2 text-xs leading-normal text-gray-500 dark:text-gray-400">
                Tituo da Instrucao 
              </p>
              <p className="text-sm font-medium text-gray-800 dark:text-white/90">
                Texto da Instrucao
              </p>
            </div>
          </div>
        </div>

        <div className="felx items-center justify-center ">
            <Button className=' font-medium'>Ativar 2FA</Button>
        </div>
      </div>
    </div>
  )
}

export default User2FA